<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
    /**
     * Tampilkan halaman profil admin.
     */
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        return view('Admin.profil', compact('admin'));
    }

    /**
     * Proses update nama & e-mail.
     */
    public function update(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $request->validate(
            [
                'name'  => 'required|string|max:100',
                'email' => 'required|email|unique:admin,email,' . $admin->id,
            ],
            [
                'name.required'  => 'Nama wajib diisi.',
                'name.string'    => 'Nama harus berupa teks.',
                'name.max'       => 'Nama maksimal :max karakter.',
                'email.required' => 'Email wajib diisi.',
                'email.email'    => 'Format email tidak valid.',
                'email.unique'   => 'Email sudah terdaftar.',
            ]
        );

        $input = [
            'name'  => $request->name,
            'email' => $request->email,
        ];

        // Cek apakah ada perubahan
        $noChanges = true;
        foreach ($input as $key => $value) {
            if ($admin->{$key} !== $value) {
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

    /**
     * Tampilkan form ubah password.
     */
    public function passwordForm()
    {
        return view('Admin.ubahPassword');
    }

    /**
     * Proses update password admin.
     * Memastikan:
     * - current_password diisi
     * - password baru diisi & minimal 6 karakter
     * - konfirmasi password wajib diisi & sama dengan password
     */
    public function updatePassword(Request $request)
    {
        $request->validate(
            [
                'current_password'      => 'required',
                'password'              => 'required|min:6',
                'password_confirmation' => 'required|same:password',
            ],
            [
                'current_password.required'      => 'Password lama wajib diisi.',
                'password.required'              => 'Password baru wajib diisi.',
                'password.min'                   => 'Password minimal :min karakter.',
                'password_confirmation.required' => 'Konfirmasi password wajib diisi.',
                'password_confirmation.same'     => 'Konfirmasi password tidak sesuai.',
            ]
        );

        $admin = Auth::guard('admin')->user();

        // Verifikasi password lama
        if (! Hash::check($request->current_password, $admin->password)) {
            return back()
                ->withErrors(['current_password' => 'Password lama tidak sesuai.'])
                ->withInput();
        }

        // Pastikan password baru tidak sama dengan lama
        if (Hash::check($request->password, $admin->password)) {
            return back()
                ->withErrors(['password' => 'Password baru tidak boleh sama dengan password lama.'])
                ->withInput();
        }

        // Simpan password baru
        $admin->password = Hash::make($request->password);
        $admin->save();

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
