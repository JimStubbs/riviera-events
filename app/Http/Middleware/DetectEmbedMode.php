<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class DetectEmbedMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $isEmbed = $request->boolean('embed') || $request->header('X-Embed') === 'true';

        config(['embed.mode' => $isEmbed]);
        View::share('isEmbed', $isEmbed);

        return $next($request);
    }
}
