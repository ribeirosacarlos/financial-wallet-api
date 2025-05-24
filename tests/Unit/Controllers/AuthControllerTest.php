<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\AuthController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_success()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $request = new Request($data);
        $controller = new AuthController();
        $response = $controller->register($request);

        $this->assertEquals(201, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('user', $content);
        $this->assertArrayHasKey('token', $content);

        $user = User::find($content['user']['id']);
        $this->assertNotNull($user);
        $this->assertEquals($data['name'], $user->name);
        $this->assertEquals($data['email'], $user->email);
        $this->assertTrue(Hash::check($data['password'], $user->password));
    }

    public function test_register_validation_fails()
    {
        $data = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ];

        $request = new Request($data);
        $controller = new AuthController();
        $response = $controller->register($request);

        $this->assertEquals(422, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertArrayHasKey('errors', $content);
    }

    public function test_login_success()
    {
        // Criar um usuário para logar
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $data = [
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $request = new Request($data);
        $controller = new AuthController();
        $response = $controller->login($request);

        $this->assertEquals(200, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('user', $content);
        $this->assertArrayHasKey('token', $content);
    }

    public function test_login_validation_fails()
    {
        $data = [
            'email' => '',
            'password' => '',
        ];

        $request = new Request($data);
        $controller = new AuthController();
        $response = $controller->login($request);

        $this->assertEquals(422, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertArrayHasKey('errors', $content);
    }

    public function test_login_invalid_credentials()
    {
        $data = [
            'email' => 'john@example.com',
            'password' => 'wrong-password',
        ];

        $request = new Request($data);
        $controller = new AuthController();
        $response = $controller->login($request);

        $this->assertEquals(401, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Usuário não identificado', $content['message']);
    }

    public function test_logout_success()
    {
        // Criar e autenticar um usuário
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);
        $token = $user->createToken('api_token')->plainTextToken;

        $request = new Request();
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        $request->headers->set('Authorization', 'Bearer ' . $token);

        $controller = new AuthController();
        $response = $controller->logout($request);

        $this->assertEquals(200, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Logout realizado com sucesso', $content['message']);
    }
}