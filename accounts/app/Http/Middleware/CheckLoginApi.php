<?php

namespace App\Http\Middleware;

use Firebase\JWT\JWT;
use App\Models\Clients;
use Closure;
use Exception;
use Firebase\JWT\Key;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
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
                    $password = $decoded->password;
                    $id = $decoded->customer_id;
                    $ktToken = DB::table('tbl_session_login')
                        ->where('id_client', $id)
                        ->where('token', $token)
                        ->get()->first();
                    if (empty($ktToken)) {
                        return response()->json(['error' => 'Token is incorrect 1.'], 401);
                    }
                    $ktLogin = DB::table('tbl_clients')->where('id', $id)->first();
                    if (!empty($ktLogin)) {
                        if (!empty($password) && !empty($ktLogin->password)) {
                            if (decrypt($password) == decrypt($ktLogin->password)) {
                                $client_id = $ktLogin->id;
                            } else {
                                return response()->json(['error' => 'Token is incorrect 2.'], 401);
                            }
                        }  else {
                            return response()->json(['error' => 'Token is incorrect 3.'], 401);
                        }
                    }
                    $dtClient = Clients::find($client_id);
                    $dtClient->token = $token;
                    if (empty($dtClient)) {
                        return response()->json(['error' => 'Token is incorrect 4.'], 401);
                    } else {
                        $request->client = $dtClient;
                        return $next($request);
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
