<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
    public function edit()
    {
        return view('pelanggan.editProfil', [
            'pelanggan' => Auth::user(),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'regex:/^[1-9][0-9]{8,14}$/'],
            'address' => ['required', 'string'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'phone.required' => 'Nomor HP wajib diisi.',
            'phone.regex' => 'Nomor HP harus berupa angka dan 9–15 digit.',
            'address.required' => 'Alamat wajib diisi.',
        ]);

        $pelanggan = Auth::user();

        $input = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => '0' . ltrim($request->phone, '0'),
            'address' => $request->address,
        ];

        $emailBerubah = $pelanggan->email !== $request->email;

        $tidakBerubah = true;
        foreach ($input as $key => $value) {
            if ($pelanggan->{$key} !== $value) {
                $tidakBerubah = false;
                break;
            }
        }

        if ($tidakBerubah) {
            return back()->withErrors(['profil' => 'Tidak ada data yang diubah.']);
        }

        $pelanggan->fill($input);

        if ($emailBerubah) {
            $pelanggan->email_verified_at = null;
            $pelanggan->save();
            $pelanggan->sendEmailVerificationNotification();
            return back()->with('success', 'Silakan cek email Anda dan lakukan verifikasi melalui link yang telah dikirim.');
        }

        $pelanggan->save();

        return back()->with('success', 'Profil berhasil diperbarui');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $pelanggan = Auth::user();

        if (!Hash::check($request->current_password, $pelanggan->password)) {
            return back()->withErrors([
                'current_password' => 'Password lama tidak sesuai.',
            ])->withInput();
        }

        if (Hash::check($request->password, $pelanggan->password)) {
            return back()->withErrors([
                'password' => 'Password baru tidak boleh sama dengan password lama.',
            ])->withInput();
        }

        $pelanggan->password = Hash::make($request->password);
        $pelanggan->save();

        return back()->with('success', 'Password berhasil diperbarui');
    }
}
