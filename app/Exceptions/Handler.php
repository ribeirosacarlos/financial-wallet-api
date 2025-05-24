<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use InvalidArgumentException;
use Throwable;
use Illuminate\Support\Arr;

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
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $e)
    {
        // Se a requisição espera JSON ou é uma API
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle API exceptions and return standardized JSON responses
     */
    private function handleApiException($request, Throwable $exception)
    {
        $statusCode = 500;
        $response = [
            'success' => false,
            'message' => 'Erro interno do servidor.'
        ];

        // Tratamento específico para cada tipo de exceção
        if ($exception instanceof ValidationException) {
            $statusCode = 422;
            $response['message'] = 'Os dados fornecidos são inválidos.';
            $response['errors'] = $exception->validator->errors()->toArray();
        } elseif ($exception instanceof AuthenticationException) {
            $statusCode = 401;
            $response['message'] = 'Não autenticado.';
        } elseif ($exception instanceof AuthorizationException) {
            $statusCode = 403;
            $response['message'] = 'Não autorizado.';
        } elseif ($exception instanceof ModelNotFoundException) {
            $statusCode = 404;
            $model = strtolower(class_basename($exception->getModel()));
            $response['message'] = "Não foi possível encontrar {$model} com o ID especificado.";
        } elseif ($exception instanceof NotFoundHttpException) {
            $statusCode = 404;
            $response['message'] = 'A URL solicitada não foi encontrada.';
        } elseif ($exception instanceof MethodNotAllowedHttpException) {
            $statusCode = 405;
            $response['message'] = 'O método especificado não é permitido para esta rota.';
        } elseif ($exception instanceof InvalidArgumentException) {
            // Aqui tratamos o seu caso específico de "Saldo insuficiente"
            $statusCode = 400;
            $response['message'] = $exception->getMessage();
        }

        // Remove detalhes do erro em ambiente de produção
        if (!config('app.debug')) {
            unset($response['exception']);
            unset($response['file']);
            unset($response['line']);
            unset($response['trace']);
        } else {
            $response['debug'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => collect($exception->getTrace())->map(function ($trace) {
                    return Arr::except($trace, ['args']);
                })->all()
            ];
        }

        return response()->json($response, $statusCode);
    }
}