<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        return view('Admin.profil', compact('admin'));
    }

    public function update(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $normalizedPhone = $this->normalizePhoneTo08($request->input('phone'));
        $request->merge(['phone' => $normalizedPhone]);

        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:admin,email,' . $admin->id,
            'phone' => [
                'required',
                'regex:/^08\d{8,11}$/',
                'unique:admin,phone,' . $admin->id,
            ],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal :max karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'phone.required' => 'Nomor HP wajib diisi.',
            'phone.regex' => 'Nomor HP harus diawali 08 dan 10–13 digit.',
            'phone.unique' => 'Nomor HP sudah digunakan.',
        ]);

        $input = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        $noChanges = true;
        foreach ($input as $key => $value) {
            $current = $admin->{$key} ?? null;
            if ($current !== $value) {
                $noChanges = false;
                break;
            }
        }
        if ($noChanges) {
            return back()->withErrors(['profil' => 'Tidak ada data yang diubah.']);
        }

        $emailChanged = $admin->email !== $request->email;
        $admin->fill($input);

        if ($emailChanged) {
            $admin->email_verified_at = null;
            $admin->save();
            $admin->sendEmailVerificationNotification();
            return back()->with('success', 'Silakan cek email Anda dan verifikasi melalui link yang telah dikirim.');
        }

        $admin->save();
        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function passwordForm()
    {
        return view('Admin.ubahPassword');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password',
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal :min karakter.',
            'password_confirmation.required' => 'Konfirmasi password wajib diisi.',
            'password_confirmation.same' => 'Konfirmasi password tidak sesuai.',
        ]);

        $admin = Auth::guard('admin')->user();

        if (!Hash::check($request->current_password, $admin->password)) {
            return back()
                ->withErrors(['current_password' => 'Password lama tidak sesuai.'])
                ->withInput();
        }

        if (Hash::check($request->password, $admin->password)) {
            return back()
                ->withErrors(['password' => 'Password baru tidak boleh sama dengan password lama.'])
                ->withInput();
        }

        $admin->password = Hash::make($request->password);
        $admin->save();

        return back()->with('success', 'Password berhasil diperbarui.');
    }

    private function normalizePhoneTo08(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }
        $phone = trim($phone);
        if ($phone === '') {
            return null;
        }

        if (strpos($phone, '+') === 0) {
            $digits = '+' . preg_replace('/\D+/', '', substr($phone, 1));
        } else {
            $digits = preg_replace('/\D+/', '', $phone);
        }

        if (strpos($digits, '+62') === 0) {
            return '0' . substr($digits, 3);
        }
        if (strpos($digits, '62') === 0) {
            return '0' . substr($digits, 2);
        }
        if (strpos($digits, '0') === 0) {
            return $digits;
        }
        if (strpos($digits, '8') === 0) {
            return '0' . $digits;
        }

        return '0' . ltrim($digits, '0');
    }
}
