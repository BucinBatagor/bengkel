<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function showLoginForm(Request $request)
    {
        $baseUrl  = url('/');
        $prev     = url()->previous();
        $current  = url()->current();

        if ($prev && Str::startsWith($prev, $baseUrl) && $prev !== $current) {
            $path = parse_url($prev, PHP_URL_PATH) ?? '/';
            if (Str::startsWith($path, ['/produk', '/katalog'])) {
                session(['login_intended' => $prev]);
            }
        }

        if (!session()->has('login_intended')) {
            session(['login_intended' => session('last_catalog_url', route('katalog'))]);
        }

        return view('Pelanggan.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::guard('pelanggan')->attempt($credentials, $remember)) {
            $user = Auth::guard('pelanggan')->user();

            if (!$user->hasVerifiedEmail()) {
                Auth::guard('pelanggan')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Email Anda belum diverifikasi. Silahkan cek kotak masuk email anda',
                ])->withInput($request->only('email'));
            }

            $request->session()->regenerate();

            $target = session()->pull('login_intended', route('katalog'));

            $baseUrl = url('/');
            if (!Str::startsWith($target, $baseUrl)) {
                $target = route('katalog');
            } else {
                $path = parse_url($target, PHP_URL_PATH) ?? '/';
                if (Str::startsWith($path, ['/login', '/register', '/password'])) {
                    $target = route('katalog');
                }
            }

            return redirect()->to($target);
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::guard('pelanggan')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/beranda');
    }
}
