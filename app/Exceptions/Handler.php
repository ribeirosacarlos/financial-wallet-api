<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Throwable;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            Log::error('Exception: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        });

        $this->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*')) { // Verifica se a requisição é para a API
                $statusCode = 500;
                $message = 'Server error occurred';
                $error_id = uniqid('err_');
                $debug = config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null;

                // Adiciona informações da requisição se estiverem disponíveis
                if (isset($e->requestInfo)) {
                    Log::info('Request Info: ', $e->requestInfo);
                    $debug['request'] = $e->requestInfo;
                }

                if ($e instanceof ValidationException) {
                    $statusCode = 422;
                    $message = 'Validation failed';
                    $debug = $e->errors();
                } elseif ($e instanceof HttpException) {
                    $statusCode = $e->getStatusCode();
                    $message = $e->getMessage() ?: 'HTTP Error';
                    $debug = null;
                }

                return response()->json([
                    'message' => $message,
                    'error_id' => $error_id,
                    'debug' => $debug
                ], $statusCode);
            }
        });
    }
}