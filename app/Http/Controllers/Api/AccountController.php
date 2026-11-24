<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Http\Resources\TransferResource;
use App\Models\Account;
use App\Models\Transfer;

class AccountController extends Controller
{
    public function show(Account $account)
    {
        return new AccountResource($account);
    }

    public function transfers(Account $account)
    {
        $transfers = Transfer::where('from_account_id', $account->id)
            ->orWhere('to_account_id', $account->id)
            ->latest()
            ->paginate(15);

        return TransferResource::collection($transfers);
    }
}
