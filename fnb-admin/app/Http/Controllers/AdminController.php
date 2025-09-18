<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Cookie;

class AdminController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function index()
    {
        return view('admin.index');
    }

    public function get_login()
    {
        if (Auth::guard('admin')->check()) {
            return redirect('admin/dashboard');
        }
        return view('admin.login');
    }

    public function post_login(LoginRequest $loginRequest)
    {

        $email = $loginRequest->input('email');
        $password = $loginRequest->input('password');
        $remember = $loginRequest->input('remember');
        $user = User::where('email', $email)->where('active', 1)->first();

        if ($user === null || !Hash::check($password, $user->password)) {
            return redirect()->back()->with('message', lang('email_password_error'));
        } else {
            Auth::guard('admin')->login($user);
            $privateKey = file_get_contents(storage_path('keys/private.pem'));

            $payload = [
                'user_id' => $user->id,
                'customer_name' => $user->name,
                'guard' => 'admin',
                'date' => date('Y-m-d H:i:s'),
            ];

            $token = JWT::encode($payload, $privateKey, 'RS256');
            $user->token = $token;
            $user->save();
            $this->request->user = $user;
            if (!empty($remember)) {
                setcookie('remember_login', json_encode([
                    'email' => $email,
                    'password' => encrypt($password),
                    'remember' => $remember,
                ]), time() + (86400 * 30), "/"); // 86400 = 1 day
            } else {
                setcookie('remember_login', null, -1, "/");
            }
            return redirect('admin/dashboard');
        }
    }

    public function get_logout()
    {
        Auth::guard('admin')->logout();
        return redirect('admin/login');
    }

    public function loadDataCustomerClass($id, $type)
    {
        $title = lang('c_title_client');
        return view('admin.dashboard.client', [
            'title' => $title,
            'type' => $type,
            'object_id' => $id
        ]);
    }

}
