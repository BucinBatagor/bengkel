<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
    public function edit()
    {
        return view('pelanggan.profil', [
            'pelanggan' => Auth::user(),
        ]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $pelanggan = Auth::user();

        $data = $request->safe()->only(['name', 'email', 'phone', 'address']);
        $pelanggan->fill($data);

        if ($request->filled('password')) {
            $pelanggan->password = Hash::make($request->input('password'));
        }

        $pelanggan->save();

        return back()->with('success', 'Profil berhasil diperbarui');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $pelanggan = Auth::user();
        $pelanggan->password = Hash::make($request->password);
        $pelanggan->save();

        return back()->with('success', 'Password berhasil diperbarui');
    }
}
