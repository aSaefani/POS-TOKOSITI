<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Vite;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = Vite::useCspNonce();
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; img-src 'self' data: blob:; style-src 'self' 'unsafe-inline' https://fonts.bunny.net; "
            ."font-src 'self' https://fonts.bunny.net data:; script-src 'self' 'unsafe-eval' 'nonce-{$nonce}'; connect-src 'self'; frame-ancestors 'none'"
        );

        return $response;
    }
}
