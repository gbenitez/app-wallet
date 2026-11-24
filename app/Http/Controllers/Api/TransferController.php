<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransferRequest;
use App\Http\Resources\TransferResource;
use App\Services\TransferService;

class TransferController extends Controller
{
    public function store(StoreTransferRequest $request, TransferService $transferService)
    {
        $transfer = $transferService->execute(
            $request->from_account_id, $request->to_account_id, $request->amount
        );

        return new TransferResource($transfer)->response()->setStatusCode(201);
    }
}
