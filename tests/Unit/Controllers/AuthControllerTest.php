<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\AuthController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\LogoutRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use DatabaseTransactions;
    
    protected $authService;
    protected $controller;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = Mockery::mock(AuthService::class);
        $this->controller = new AuthController($this->authService);
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_register_success()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
        
        $user = new User([
            'id' => 1,
            'name' => $data['name'],
            'email' => $data['email'],
        ]);
        
        $serviceResponse = [
            'user' => $user,
            'token' => 'test_token'
        ];
        
        $request = Mockery::mock(RegisterRequest::class);
        $request->shouldReceive('validated')->once()->andReturn($data);
        
        $this->authService->shouldReceive('register')
            ->once()
            ->with($data)
            ->andReturn($serviceResponse);
        
        $response = $this->controller->register($request);
        
        $this->assertEquals(201, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('user', $content);
        $this->assertArrayHasKey('token', $content);
        $this->assertEquals('test_token', $content['token']);
    }

    public function test_login_success()
    {
        $data = [
            'email' => 'john@example.com',
            'password' => 'password123',
        ];
        
        $user = new User([
            'id' => 1,
            'name' => 'John Doe',
            'email' => $data['email'],
        ]);
        
        $serviceResponse = [
            'user' => $user,
            'token' => 'test_token'
        ];
        
        $request = Mockery::mock(LoginRequest::class);
        $request->shouldReceive('validated')->once()->andReturn($data);
        
        $this->authService->shouldReceive('login')
            ->once()
            ->with($data)
            ->andReturn($serviceResponse);
        
        $response = $this->controller->login($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('user', $content);
        $this->assertArrayHasKey('token', $content);
        $this->assertEquals('test_token', $content['token']);
    }

    public function test_logout_success()
    {
        $request = Mockery::mock(LogoutRequest::class);
        $request->shouldReceive('all')->once()->andReturn([]);
        
        $this->authService->shouldReceive('logout')
            ->once()
            ->with([]);
        
        $response = $this->controller->logout($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Logout realizado com sucesso', $content['message']);
    }
}