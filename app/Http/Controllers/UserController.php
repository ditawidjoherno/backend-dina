<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
public function getUserData(Request $request)
{
    // Autentikasi user dari token JWT
    $user = JWTAuth::parseToken()->authenticate();

    if ($user) {
        return response()->json([
            'user' => $user
        ], 200);
    }

    return response()->json([
        'error' => 'User not authenticated'
    ], 401);
}

    
    public function getUserCounts()
    {
        try {
            $jumlahSiswa = DB::table('users')->where('role', 'siswa')->count();
            $jumlahGuru = DB::table('users')->where('role', 'guru')->count();
            $jumlahTotal = DB::table('users')->count();
    
            return response()->json([
                'siswa' => $jumlahSiswa,
                'guru' => $jumlahGuru,
                'total' => $jumlahTotal,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal mengambil data',
                'message' => $e->getMessage(),
            ], 500);
        }
}

public function getAllSiswa()
    {
        // Jika menggunakan field 'role' langsung di tabel users
        $siswa = User::where('role', 'siswa')->get();

        // Jika menggunakan Spatie Laravel Permission
        // $siswa = User::role('siswa')->get();

        return response()->json([
            'status' => 'success',
            'data' => $siswa
        ], 200);
    }
public function getAllGuru()
    {
        // Jika menggunakan field 'role' langsung di tabel users
        $guru = User::where('role', 'guru')->get();

        // Jika menggunakan Spatie Laravel Permission
        // $siswa = User::role('siswa')->get();

        return response()->json([
            'status' => 'success',
            'data' => $guru
        ], 200);
    }
    public function getUsersWithTotal()
    {
        // Mengambil seluruh data user
        $users = User::all();
    
        // Mengambil jumlah total user
        $totalUsers = $users->count();
    
        // Proses menambahkan data NIP dan NISN sesuai kondisi
        $usersWithDetails = $users->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->nama,
                'gender' => $user->jenis_kelamin,
                'class' => $user->kelas,
                'nip' => $user->nip ? $user->nip : null, // Jika ada NIP, tampilkan, jika tidak tampilkan null
                'nisn' => $user->nisn ? $user->nisn : null, // Jika ada NISN, tampilkan, jika tidak tampilkan null
            ];
        });
    
        // Mengembalikan response JSON dengan data user dan total count
        return response()->json([
            'status' => 'success',
            'total_users' => $totalUsers,
            'data' => $usersWithDetails,
        ], 200);
    }


public function getProfile(Request $request)
{
    $user = JWTAuth::parseToken()->authenticate(); // Ambil user dari token JWT

    return response()->json([
        'status' => 'success',
        'data' => $user,
    ], 200);
}

public function updateProfile(Request $request)
{
    // Ambil user yang sedang login
    $user = auth()->user();

    // Validasi input data
    $validator = Validator::make($request->all(), [
        'nama' => 'sometimes|string|max:255',
        'email' => 'sometimes|email|unique:users,email,' . $user->id,
        'foto_profil' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi untuk foto_profil
        'nomor_hp' => 'sometimes|string|max:15',
        'agama' => 'sometimes|string|max:255',
        'jenis_kelamin' => 'sometimes|in:L,P',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validasi gagal.',
            'errors' => $validator->errors()
        ], 422);
    }

    // Update data jika ada dalam request
    $data = $request->only([
        'nama', 'email', 'nomor_hp', 'agama', 'jenis_kelamin'
    ]);
    $user->fill($data);

    // Jika ada upload foto baru
    if ($request->hasFile('foto_profil')) {
        // Hapus foto lama jika ada
        if ($user->foto_profil && Storage::disk('public')->exists($user->foto_profil)) {
            Storage::disk('public')->delete($user->foto_profil);
        }

        // Upload file dan simpan pathnya
        $path = $request->file('foto_profil')->store('profiles', 'public');
        $user->foto_profil = $path;
    }

    // Simpan perubahan user
    $user->save();

    // Kembalikan response
    return response()->json([
        'message' => 'Profil berhasil diperbarui.',
        'user' => $user,
    ]);
}

public function siswaGender()
    {
        $lakiLaki = User::where('role', 'siswa')->where('jenis_kelamin', 'L')->count();
        $perempuan = User::where('role', 'siswa')->where('jenis_kelamin', 'P')->count();

        return response()->json([
            'laki_laki' => $lakiLaki,
            'perempuan' => $perempuan,
        ]);
    }

    // Endpoint untuk guru
    public function guruGender()
    {
        $lakiLaki = User::where('role', 'guru')->where('jenis_kelamin', 'L')->count();
        $perempuan = User::where('role', 'guru')->where('jenis_kelamin', 'P')->count();

        return response()->json([
            'laki_laki' => $lakiLaki,
            'perempuan' => $perempuan,
        ]);
    }


}
