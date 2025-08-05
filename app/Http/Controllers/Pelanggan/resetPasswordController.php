<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);

        $status = Password::broker('pelanggan')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
                Auth::guard('pelanggan')->login($user);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('beranda')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
