<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Présence en ligne (messagerie instantanée) : met à jour users.last_seen_at
 * au plus une fois par minute, sans toucher updated_at.
 */
class TouchLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && (!$user->last_seen_at || $user->last_seen_at->lt(now()->subMinute()))) {
            DB::table('users')->where('id', $user->id)->update(['last_seen_at' => now()]);
        }
        return $next($request);
    }
}
