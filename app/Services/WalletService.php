<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function deposit(User $user, float $amount)
    {
        if ($amount < 0.01) {
            throw new \InvalidArgumentException('Valor inválido para depósito.');
        }

        DB::transaction(function () use ($user, $amount) {
            $user->balance += $amount;
            $user->save();

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'deposit',
                'amount' => $amount,
            ]);
        });

        return $user->fresh()->balance;
    }

    public function transfer(User $sender, string $recipientEmail, float $amount)
    {
        if ($amount < 0.01) {
            throw new \InvalidArgumentException('Valor inválido para transferência.');
        }

        $recipient = User::where('email', $recipientEmail)->firstOrFail();

        if ($sender->id === $recipient->id) {
            throw new \InvalidArgumentException('Não é possível transferir para você mesmo.');
        }

        if ($sender->balance < $amount) {
            throw new \InvalidArgumentException('Saldo insuficiente.');
        }

        DB::transaction(function () use ($sender, $recipient, $amount) {
            $sender->balance -= $amount;
            $sender->save();

            $recipient->balance += $amount;
            $recipient->save();

            Transaction::create([
                'user_id' => $sender->id,
                'type' => 'transfer',
                'amount' => $amount,
                'related_user_id' => $recipient->id,
            ]);

            Transaction::create([
                'user_id' => $recipient->id,
                'type' => 'deposit',
                'amount' => $amount,
                'related_user_id' => $sender->id,
            ]);
        });

        return $sender->fresh()->balance;
    }

    public function reverse(int $transactionId)
    {
        $transaction = Transaction::findOrFail($transactionId);

        if ($transaction->reversed) {
            throw new \InvalidArgumentException('Transaction already reversed');
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

        return true;
    }
}