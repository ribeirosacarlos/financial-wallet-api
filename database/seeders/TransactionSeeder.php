<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\User;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        // Create some deposits
        foreach ($users as $user) {
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'deposit',
                'amount' => $user->balance,
                'reversed' => false,
            ]);
        }

        // Create some transfers between users
        if ($users->count() >= 2) {
            // Transfer from John to Jane
            Transaction::create([
                'user_id' => $users[1]->id, // John
                'type' => 'transfer',
                'amount' => 100.00,
                'related_user_id' => $users[2]->id, // Jane
                'reversed' => false,
            ]);

            // Transfer from Jane to Bob
            Transaction::create([
                'user_id' => $users[2]->id, // Jane
                'type' => 'transfer',
                'amount' => 50.00,
                'related_user_id' => $users[3]->id, // Bob
                'reversed' => false,
            ]);

            // Create a reversed transaction
            Transaction::create([
                'user_id' => $users[1]->id, // John
                'type' => 'transfer',
                'amount' => 25.00,
                'related_user_id' => $users[3]->id, // Bob
                'reversed' => true,
            ]);
        }
    }
} 