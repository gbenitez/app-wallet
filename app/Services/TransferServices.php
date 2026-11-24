<?php

namespace App\Services;

use App\Exceptions\TransferException;
use App\Models\Account;
use App\Models\Transfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransferServices
{
    public function execute(int $fromId, int $toId, string $amount): Transfer|TransferException
    {
        try {

            return DB::transaction(function () use ($fromId, $toId, $amount) {
                if ($fromId === $toId) {
                    throw TransferException::sameAccount();
                }
                // Bloquea las dos filas, siempre en orden de id, hasta el commit
                $accounts = Account::whereIn('id', [$fromId, $toId])->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                $from = $accounts[$fromId];
                $to = $accounts[$toId];
                if (! $from->isActive() || ! $to->isActive()) {
                    throw TransferException::inactiveAccount();
                }
                if (bccomp($from->balance, $amount, 2) < 0) {
                    throw TransferException::insufficientFunds();
                }

                $from->balance = bcsub($from->balance, $amount, 2);
                $to->balance = bcadd($to->balance, $amount, 2);
                $from->save();
                $to->save();

                return Transfer::create([
                    'reference' => (string) Str::uuid(),
                    'from_account_id' => $fromId,
                    'to_account_id' => $toId,
                    'amount' => $amount,
                    'status' => 'completed',
                ]);
            });
        } catch (TransferException $e) {
            Transfer::create([
                'reference' => (string) Str::uuid(),
                'from_account_id' => $fromId,
                'to_account_id' => $toId,
                'amount' => $amount,
                'status' => 'failed',
                'failure_reason' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
