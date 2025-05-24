<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepositRequest;
use App\Http\Requests\TransferRequest;
use App\Models\Transaction;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function deposit(DepositRequest $request)
    {
        $user = $request->user();
        $amount = $request->input('amount');

        $balance = $this->walletService->deposit($user, $amount);

        Log::info('User deposited', ['user_id' => $user->id, 'amount' => $amount]);

        return response([
            'message' => 'Deposito realizado com sucesso!',
            'balance' => $balance
        ]);
    }

    public function transfer(TransferRequest $request)
    {
        $sender = $request->user();
        $recipientEmail = $request->input('recipient_email');
        $amount = $request->input('amount');

        $balance = $this->walletService->transfer($sender, $recipientEmail, $amount);

        Log::info('User transferred', [
            'sender_id' => $sender->id,
            'recipient_email' => $recipientEmail,
            'amount' => $amount
        ]);

        return response([
            'message' => 'Tranferencia realizada com sucesso!',
            'balance' => $balance
        ]);
    }

    public function reverse(Request $request, $id)
    {
        $this->walletService->reverse($id);

        return response(['message' => 'Transaction reversed']);
    }
}