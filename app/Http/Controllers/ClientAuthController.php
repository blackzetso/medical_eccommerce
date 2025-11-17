<?php
namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientAuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('/');
        }
        return Inertia::render('Client/Login');
    }

    public function login(Request $request)
    {
        // تحقق من بيانات الدخول
        $credentials = $request->only('email', 'password');
        if (Auth::guard('web')->attempt($credentials)) {
            return redirect()->intended(route('/'));
        }
        return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة']);
    }

    public function showRegisterForm()
    {
        return view('client.register');
    }

    public function register(Request $request)
    {
        // تسجيل مستخدم جديد
        $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);
        $user = User::create([
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        Auth::guard('web')->login($user);
        return redirect('/client/myorders');
    }
}
