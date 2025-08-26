<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AutoLogout
{
    public function handle(Request $request, Closure $next)
    {
        if (!empty($request->client)) {

            $lastActivity = session('last_activity_'.$request->client->id.'');
//            if ($lastActivity->diffInMinutes(Carbon::now()) > 30) {
//                Auth::logout();
//                session()->flush();
//                return redirect()->route('login');
//            }
        }

        session(['last_activity' => Carbon::now()]);

        return $next($request);
    }
}
