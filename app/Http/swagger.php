<?php

namespace App\Http;

/**
 * @OA\Info(
 *     title="Wallet API",
 *     version="1.0.0",
 *     description="API para gerenciamento de carteira digital",
 *     @OA\Contact(
 *         email="seu-email@exemplo.com",
 *         name="Suporte API"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="/api",
 *     description="API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class Swagger
{
}