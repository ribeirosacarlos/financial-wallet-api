<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data)
    {
        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $token = $user->createToken('api_token')->plainTextToken;
            DB::commit();

            return [
                'user' => $user,
                'token' => $token,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e; // Lança a exceção para ser tratada globalmente
        }
    }

    public function login(array $data)
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            throw new \Exception('Usuário não identificado');
        }

        if (!Hash::check($data['password'], $user->password)) {
            throw new \Exception('Credenciais inválidas');
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout(array $data)
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            throw new \Exception('Usuário não identificado');
        }

        $user->tokens()->delete();

        return [
            'message' => 'Logout realizado com sucesso',
        ];
    }
}