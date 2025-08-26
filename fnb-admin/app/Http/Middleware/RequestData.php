<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class RequestData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->header('locale') ?? 'vn';
        if (!in_array($locale, ['vn', 'en', 'zh','ko','ja'])) {
            $locale = 'vn';
        }

        $request->_locale = $locale ;
        App::setLocale($locale);

        return $next($request);
    }
}
