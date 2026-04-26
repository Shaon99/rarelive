<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WebhookAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = config('services.steadfast.webhook_secret');
        // Check if Authorization header is present
        $authHeader = $request->header('Authorization');

        if (! $authHeader || ! str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // Extract the token
        $token = substr($authHeader, 7);

        // Verify the token
        if ($token !== $apiKey) {
            return response()->json(['error' => 'Invalid API Key'], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
