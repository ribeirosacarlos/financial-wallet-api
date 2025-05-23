<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Wallet",
 *     description="API Endpoints para operações de carteira"
 * )
 */
class WalletController extends Controller
{
    /**
     * @OA\Post(
     *     path="/wallet/deposit",
     *     summary="Depositar valor na carteira",
     *     description="Adiciona um valor à carteira do usuário autenticado",
     *     operationId="deposit",
     *     tags={"Wallet"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(property="amount", type="number", format="float", example=100.50, description="Valor a ser depositado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Depósito realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Deposito realizado com sucesso!"),
     *             @OA\Property(property="balance", type="number", format="float", example=500.75)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro na requisição",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Não foi possível depositar, valor negativo.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/wallet/transfer",
     *     summary="Transferir valor para outro usuário",
     *     description="Transfere um valor da carteira do usuário autenticado para outro usuário",
     *     operationId="transfer",
     *     tags={"Wallet"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount", "recipient_email"},
     *             @OA\Property(property="amount", type="number", format="float", example=50.00, description="Valor a ser transferido"),
     *             @OA\Property(property="recipient_email", type="string", format="email", example="destinatario@exemplo.com", description="Email do destinatário")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transferência realizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tranferencia realizada com sucesso!"),
     *             @OA\Property(property="balance", type="number", format="float", example=450.75)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro na requisição",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Saldo insuficiente.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/wallet/reverse/{id}",
     *     summary="Reverter uma transação",
     *     description="Reverte uma transação específica pelo ID",
     *     operationId="reverse",
     *     tags={"Wallet"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da transação a ser revertida",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transação revertida com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Transaction reversed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro na requisição",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Transaction already reversed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Transação não encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Transaction] ID")
     *         )
     *     )
     * )
     */
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