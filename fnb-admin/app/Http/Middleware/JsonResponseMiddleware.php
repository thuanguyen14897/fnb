<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class JsonResponseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($response instanceof JsonResponse || ($response instanceof Response && $response->headers->get('Content-Type') === 'application/json')) {
            $response->headers->set('Content-Type', 'application/json;charset=UTF-8');
            $response->headers->set('Charset', 'utf-8');

            // Thay đổi JSON encode options
            $content = json_decode($response->getContent(), true);
            $response->setContent(json_encode($content, JSON_UNESCAPED_UNICODE));

        }

        return $response;
    }
}