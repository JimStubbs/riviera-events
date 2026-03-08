<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmbedHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (config('embed.mode')) {
            $response->headers->set('X-Frame-Options', 'ALLOWALL');
            $response->headers->set('Content-Security-Policy', "frame-ancestors *");
        }

        return $response;
    }
}
