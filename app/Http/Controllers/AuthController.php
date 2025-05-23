<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            // Validação com diagnóstico detalhado
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
            ]);
            
            if ($validator->fails()) {
                Log::warning('Validation failed', ['errors' => $validator->errors()->toArray()]);
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Verificar conexão com banco de dados
            try {
                DB::connection()->getPdo();
            } catch (\Exception $e) {
                Log::error('Database connection failed: ' . $e->getMessage());
                return response()->json(['message' => 'Database connection error'], 500);
            }
            
            // Verificar tabela users
            if (!DB::getSchemaBuilder()->hasTable('users')) {
                Log::error('Users table does not exist');
                return response()->json(['message' => 'Database schema error'], 500);
            }
            
            // Criar usuário com transação
            $userData = null;
            $token = null;
            
            DB::beginTransaction();
            try {
                $userData = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);
                
                $token = $userData->createToken('api_token')->plainTextToken;
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('User creation failed: ' . $e->getMessage());
                return response()->json(['message' => 'User creation failed: ' . $e->getMessage()], 500);
            }
            
            return response()->json([
                'user' => $userData,
                'token' => $token
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'An unexpected error occurred',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function login(RegisterRequest $request)
    {
        $fields = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $fields['email'])->first();

        if (!$user) {
            return response(['message' => 'User não identificado'], 401);
        }

        if (!Hash::check($fields['password'], $user->password)) {
            return response(['message' => 'Credenciais Invalidas'], 401);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return response([
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function logout(RegisterRequest $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response(['message' => 'Logout realizado com sucesso'], status: 200);
    }
}
