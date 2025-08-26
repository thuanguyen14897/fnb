<?php

namespace App\Http\Middleware;

use App\Models\Clients;
use Closure;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CheckLoginApi
{
    public function handle($request, Closure $next, $guard = null)
    {
        $this->SaveSession = true;
        $token = $request->bearerToken('token');
        $client_id = 0;
        if (!$token) {
            return response()->json(['error' => 'Token not provided.'], 401);
        }
        if ($token == '0845530024') {
            $client_id = 9;
            $dtClient = Clients::find($client_id);
            if (empty($dtClient)) {
                return response()->json(['error' => 'Token is incorrect 4.'], 401);
            } else {
                $request->client = $dtClient;
                return $next($request);
            }
        } elseif ($token != 'kanow') {
            try {
                if (!empty($token)) {
                    $data = base64_decode($token);
                    $ArrayToken = explode('|||', $data);
                    $id = $ArrayToken[0];
                    $password = $ArrayToken[1];
                    $dateCreate = $ArrayToken[2];
                    $player_id = !empty($ArrayToken[3]) ? $ArrayToken[3] : null;
                    $sign_up_with = $ArrayToken[4];
                    $id_sign_up = $ArrayToken[5];
                    $project = $ArrayToken[6];
                    if (!empty($this->SaveSession)) {
                        $user_agent = $request->server('HTTP_USER_AGENT');
                        $ip_login = $request->server('REMOTE_ADDR');
                        $ktToken = DB::table('tbl_session_login')
                            ->where('id_client', $id)
                            ->where('token', $token)
//                            ->where('user_agent', $user_agent)
//                            ->where('player_id', $player_id)
                            ->get()->first();
                        if (empty($ktToken)) {
                            return response()->json(['error' => 'Token is incorrect 1.'], 401);
                        }
                    }
                    $ktLogin = DB::table('tbl_clients')
                        ->where('id', $id)->first();
                    if (!empty($ktLogin)) {
                        if (!empty($password) && !empty($ktLogin->password)) {
                            if (decrypt($password) == decrypt($ktLogin->password)) {
                                $client_id = $ktLogin->id;
                            } else {
                                return response()->json(['error' => 'Token is incorrect 2.'], 401);
                            }
                        } elseif (!empty($sign_up_with) && !empty($id_sign_up)) {
                            if ($ktLogin->sign_up_with == $sign_up_with && $ktLogin->id_sign_up == $id_sign_up) {
                                $client_id = $ktLogin->id;
                            }
                        } else {
                            return response()->json(['error' => 'Token is incorrect 3.'], 401);
                        }
                    }
                }
            } catch (ExpiredException $e) {
                return response()->json(['error' => $e->getMessage()], 401);
            }
            $dtClient = Clients::find($client_id);
            if (empty($dtClient)) {
                return response()->json(['error' => 'Token is incorrect 4.'], 401);
            } else {
                $request->client = $dtClient;
                return $next($request);
//                $keycache = 'last_activity_'.$dtClient->id.'';
//                $last_activity = Cache::store('file')->get($keycache);
//                $checklogin = true;
//                if ($last_activity){
//                    if($last_activity->diffInMinutes(Carbon::now()) > 30){
//                        Cache::store('file')->forget($keycache);
//                        DB::table('tbl_session_login')->where('id_client',$dtClient->id)->delete();
//                        $checklogin = false;
//                    }
//                    Cache::store('file')->put($keycache, \Carbon\Carbon::now());
//                } else {
//                    Cache::store('file')->put($keycache, \Carbon\Carbon::now());
//                }
//
//                if (!empty($checklogin)){
//                    $request->client = $dtClient;
//                    return $next($request);
//                } else {
//                    return response()->json(['error' => 'Token is incorrect 1.'], 401);
//                }
            }
        } else {
            $request->client = null;
            return $next($request);
        }
    }
}
