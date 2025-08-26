<?php

namespace App\Http\Middleware;

use App\Models\Driver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckLoginDriverApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->SaveSession = true;
        $token = $request->bearerToken('token');
        $driver_id = 0;
        if (!$token) {
            return response()->json(['error' => 'Token not provided.'], 401);
        }
        try {
            if (!empty($token)) {
                $data = base64_decode($token);
                $ArrayToken = explode('|||', $data);
                $id = $ArrayToken[0];
                $password = $ArrayToken[1];
                $dateCreate = $ArrayToken[2];
                $sign_up_with = $ArrayToken[3];
                $id_sign_up = $ArrayToken[4];
                if (!empty($this->SaveSession)) {
                    $user_agent = $request->server('HTTP_USER_AGENT');
                    $ip_login = $request->server('REMOTE_ADDR');
                    $ktToken = DB::table('tbl_session_login_driver')
                        ->where('driver_id', $id)
                        ->where('token', $token)
                        ->get()->first();
                    if (empty($ktToken)) {
                        return response()->json(['error' => 'Token is incorrect 1.'], 401);
                    }
                }
                $ktLogin = DB::table('tbl_driver')
                    ->where('id', $id)->first();
                if (!empty($ktLogin)) {
                    if (!empty($password) && !empty($ktLogin->password)) {
                        if (decrypt($password) == decrypt($ktLogin->password)) {
                            $driver_id = $ktLogin->id;
                        } else {
                            return response()->json(['error' => 'Token is incorrect 2.'], 401);
                        }
                    } elseif (!empty($sign_up_with) && !empty($id_sign_up)) {
                        if ($ktLogin->sign_up_with == $sign_up_with && $ktLogin->id_sign_up == $id_sign_up) {
                            $driver_id = $ktLogin->id;
                        }
                    } else {
                        return response()->json(['error' => 'Token is incorrect 3.'], 401);
                    }
                }
            }
        } catch (ExpiredException $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
        $dtDriver = Driver::find($driver_id);
        if (empty($dtDriver)) {
            return response()->json(['error' => 'Token is incorrect 4.'], 401);
        } else {
            $request->driver = $dtDriver;
            return $next($request);
        }
    }
}
