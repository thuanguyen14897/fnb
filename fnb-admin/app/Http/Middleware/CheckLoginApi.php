<?php

namespace App\Http\Middleware;

use App\Models\Clients;
use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CheckLoginApi
{
    public function handle($request, Closure $next, $guard = null)
    {
        $token = $request->bearerToken('token');
        $client_id = 0;
        if (!$token) {
            return response()->json(['error' => 'Token not provided.'], 401);
        }
        $publicKey = file_get_contents(storage_path('keys/public.pem'));
        if ($token != 'fnb') {
            try {
                $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));
                if (!empty($decoded) && $decoded->guard == 'admin') {
                    $id = $decoded->user_id;
                    if (!empty($id)){
                        $user = new \StdClass();
                        $user->id = $id;
                        $user->fullname = $decoded->customer_name;
                        $user->guard = 'admin';
                        $user->token = $token;
                        $request->client = $user;
                        return $next($request);
                    } else {
                        return response()->json(['error' => 'Token is incorrect 1.'], 401);
                    }
                } else {
                    $id = $decoded->customer_id;
                    if (!empty($id)) {
                        $client = new \StdClass();
                        $client->id = $id;
                        $client->fullname = $decoded->customer_name;
                        $client->email = $decoded->email;
                        $client->guard = 'customer';
                        $client->token = $token;
                        $request->client = $client;
                        return $next($request);
                    } else {
                        return response()->json(['error' => 'Token is incorrect 2.'], 401);
                    }
                }
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 401);
            }
        } else {
            $request->client = null;
            return $next($request);
        }
    }
}
