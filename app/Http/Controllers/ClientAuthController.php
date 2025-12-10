<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Lead;
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
        return Inertia::render('Client/Signup');
    }

    public function register(Request $request)
    {
        // حفظ البيانات كـ lead بدلاً من إنشاء حساب مباشرة
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'pharmacy_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'location_url' => 'nullable|url|max:2048',
            'notes' => 'nullable|string',
        ]);

        // إنشاء lead جديد
        Lead::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'pharmacy_name' => $request->pharmacy_name,
            'address' => $request->address,
            'location_url' => $request->location_url,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        return back()->with('success', 'شكراً لك! تم إرسال طلبك بنجاح. سيقوم فريقنا بالتواصل معك قريباً لإنشاء حسابك.');
    }
}
