<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    public function showForm(Request $request, $token)
    {
        return view('pelanggan.resetPassword', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate(
            [
                'token'                 => 'required',
                'email'                 => 'required|email',
                'password'              => 'required|min:6',
                'password_confirmation' => 'required|same:password',
            ],
            [
                'token.required'                 => 'Token tidak ditemukan.',
                'email.required'                 => 'Email wajib diisi.',
                'email.email'                    => 'Email tidak valid.',
                'password.required'              => 'Password wajib diisi.',
                'password.min'                   => 'Password minimal 6 karakter.',
                'password_confirmation.required' => 'Konfirmasi password wajib diisi.',
                'password_confirmation.same'     => 'Konfirmasi password tidak cocok.',
            ]
        );

        $status = Password::broker('pelanggan')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                if (Hash::check($password, $user->password)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'password' => 'Password baru tidak boleh sama dengan password lama.',
                    ]);
                }

                $user->password = Hash::make($password);
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('password.berhasil')
            : back()->withErrors(['email' => __($status)]);
    }
}
