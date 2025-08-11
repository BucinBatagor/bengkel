<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:admin')->only(['showLoginForm', 'login']);
        $this->middleware('auth:admin')->only(['logout']);
    }

    public function showLoginForm(Request $request)
    {
        if ($request->filled('next')) {
            $next = $request->query('next');
            if ($this->isSafeRedirect($next)) {
                $request->session()->put('url.intended', $next);
            }
        }

        if (Auth::guard('admin')->check()) {
            return redirect()->intended(route('admin.pemesanan.index'));
        }

        return view('Admin.adminLogin');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Kolom email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kolom password wajib diisi.',
        ]);

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.pemesanan.index'));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function isSafeRedirect(?string $url): bool
    {
        if (!$url) {
            return false;
        }

        if (Str::startsWith($url, '//')) {
            return false;
        }

        if (Str::startsWith($url, '/')) {
            return true;
        }

        $appBase = url('/');
        if (Str::startsWith($url, $appBase)) {
            return true;
        }

        return false;
    }
}
