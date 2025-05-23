<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    public function deposit(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:0.01']);

        $user = $request->user();

        if ($user->balance < 0) {
            return response(['message' => 'Não foi possível depositar, valor negativo.'], 400);
        }

        DB::transaction(function () use ($user, $request) {
            $user->balance += $request->amount;
            $user->save();

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'deposit',
                'amount' => $request->amount,
            ]);
        });

        Log::info('User deposited', ['user_id' => $user->id, 'amount' => $request->amount]);

        return response(['message' => 'Deposito realizado com sucesso!', 'balance' => $user->fresh()->balance]);
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'recipient_email' => 'required|email|exists:users,email', 
        ]);

        $sender = $request->user();
        $recipient = User::where('email', $request->recipient_email)->first();

        if($sender->id === $recipient->id) {
            return response(['message' => 'Não é possível transferir para você mesmo.'], 400);
        }

        if($sender->balance < $request->amount) {
            return response(['message' => 'Saldo insuficiente.'], 400);
        }

        DB::transaction(function () use ($sender, $recipient, $request) {
            $sender->balance -= $request->amount;
            $sender->save();

            $recipient->balance += $request->amount;
            $recipient->save();

            Transaction::create([
                'user_id' => $sender->id,
                'type' => 'transfer',
                'amount' => $request->amount,
                'related_user_id' => $recipient->id,
            ]);

            Transaction::create([
                'user_id' => $recipient->id,
                'type' => 'deposit',
                'amount' => $request->amount,
                'related_user_id' => $sender->id,
            ]);
        });

        Log::info('User transferred', [
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'amount' => $request->amount
        ]);

        return response(['message' => 'Tranferencia realizada com sucesso!', 'balance' => $sender->fresh()->balance]);
    }

    public function reverse(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->reversed) {
            return response(['message' => 'Transaction already reversed'], 400);
        }

        DB::transaction(function () use ($transaction) {
            if ($transaction->type === 'deposit') {
                $user = $transaction->user;
                $user->balance -= $transaction->amount;
                $user->save();
            } elseif ($transaction->type === 'transfer') {
                $sender = $transaction->user;
                $recipient = $transaction->relatedUser;
                $sender->balance += $transaction->amount;
                $recipient->balance -= $transaction->amount;
                $sender->save();
                $recipient->save();
            }
            $transaction->reversed = true;
            $transaction->save();
        });

        return response(['message' => 'Transaction reversed']);
    }
}
