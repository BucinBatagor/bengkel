<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('pelanggan.register');
    }

    public function register(Request $request)
    {
        try {
            $validated = $request->validate(
                [
                    'name'                  => ['required', 'string', 'max:255'],
                    'email'                 => ['required', 'email', 'unique:pelanggan,email'],
                    'phone'                 => ['required', 'regex:/^[0-9]{9,15}$/'],
                    'address'               => ['required', 'string'],
                    'password'              => ['required', 'min:6'],
                    'password_confirmation' => ['required', 'same:password'],
                ],
                [
                    'name.required'                  => 'Nama wajib diisi.',
                    'email.required'                 => 'Email wajib diisi.',
                    'email.email'                    => 'Format email tidak valid.',
                    'email.unique'                   => 'Email sudah digunakan.',
                    'phone.required'                 => 'Nomor HP wajib diisi.',
                    'phone.regex'                    => 'Nomor HP harus berupa angka dan 9–15 digit.',
                    'address.required'               => 'Alamat wajib diisi.',
                    'password.required'              => 'Password wajib diisi.',
                    'password.min'                   => 'Password minimal 6 karakter.',
                    'password_confirmation.required' => 'Konfirmasi password wajib diisi.',
                    'password_confirmation.same'     => 'Konfirmasi password tidak cocok.',
                ]
            );

            $digitsOnly = preg_replace('/\D/', '', $validated['phone']);
            $validated['phone'] = '0' . ltrim($digitsOnly, '0');

            $user = Pelanggan::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'phone'    => $validated['phone'],
                'address'  => $validated['address'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->sendEmailVerificationNotification();

            return redirect()
                ->route('verification.notice')
                ->with('success', 'Pendaftaran berhasil. Silakan cek email untuk verifikasi.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }
}
