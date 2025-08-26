<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next,...$roles): Response
    {
       if (Auth::guard('admin')->check()){
           if (empty($roles)) {
               return redirect('admin/dashboard');
           } else {
               $request->user = Auth::guard('admin')->user();
               return $next($request);
           }
       } else {
           return redirect()->intended(route('admin.login'));
       }
    }
}
