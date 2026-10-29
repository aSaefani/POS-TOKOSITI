<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pemeriksa izin terpusat. Pemakaian di route: ->middleware('permission:reports.view')
 * Menolak dengan 403 (API/JSON) atau 403 halaman, tanpa membocorkan detail.
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active || ! $user->hasPermission($permission)) {
            abort(403);
        }

        return $next($request);
    }
}
