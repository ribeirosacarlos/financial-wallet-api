<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\WalletController;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\TransferRequest;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class WalletControllerTest extends TestCase
{
    protected $walletService;
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->walletService = Mockery::mock(WalletService::class);
        $this->controller = new WalletController($this->walletService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_deposit_success()
    {
        $user = new User(['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com']);
        $amount = 100.0;
        $balance = 200.0;

        $request = Mockery::mock(DepositRequest::class);
        $request->shouldReceive('user')->once()->andReturn($user);
        $request->shouldReceive('input')->with('amount')->once()->andReturn($amount);

        $this->walletService->shouldReceive('deposit')
            ->once()
            ->with($user, $amount)
            ->andReturn($balance);

        $response = $this->controller->deposit($request);

        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Deposito realizado com sucesso!', $content['message']);
        $this->assertEquals($balance, $content['balance']);
    }

    public function test_transfer_success()
    {
        $sender = new User(['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com']);
        $recipientEmail = 'jane@example.com';
        $amount = 50.0;
        $balance = 150.0;

        $request = Mockery::mock(TransferRequest::class);
        $request->shouldReceive('user')->once()->andReturn($sender);
        $request->shouldReceive('input')->with('recipient_email')->once()->andReturn($recipientEmail);
        $request->shouldReceive('input')->with('amount')->once()->andReturn($amount);

        $this->walletService->shouldReceive('transfer')
            ->once()
            ->with($sender, $recipientEmail, $amount)
            ->andReturn($balance);

        $response = $this->controller->transfer($request);

        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Tranferencia realizada com sucesso!', $content['message']);
        $this->assertEquals($balance, $content['balance']);
    }

    public function test_reverse_success()
    {
        $transactionId = 123;
        $request = Mockery::mock(Request::class);

        $this->walletService->shouldReceive('reverse')
            ->once()
            ->with($transactionId);

        $response = $this->controller->reverse($request, $transactionId);

        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Transaction reversed', $content['message']);
    }
}