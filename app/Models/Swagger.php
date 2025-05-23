<?php

namespace App\Models;

/**
 * @OA\Schema(
 *     schema="Transaction",
 *     required={"id", "user_id", "type", "amount", "created_at", "updated_at"},
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="user_id", type="integer", format="int64", example=10),
 *     @OA\Property(property="type", type="string", enum={"deposit", "transfer"}, example="deposit"),
 *     @OA\Property(property="amount", type="number", format="float", example=100.50),
 *     @OA\Property(property="related_user_id", type="integer", format="int64", example=15, nullable=true),
 *     @OA\Property(property="reversed", type="boolean", example=false),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="User",
 *     required={"id", "name", "email", "balance"},
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="balance", type="number", format="float", example=500.75),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Swagger
{
}