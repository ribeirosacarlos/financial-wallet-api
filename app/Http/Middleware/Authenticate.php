<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }

    /**
     * Determine if the user is allowed to pass through the middleware.
     */
    protected function shouldPassThrough(Request $request): bool
    {
        return in_array($request->route()->getName(), ['login', 'register']);
    }
}