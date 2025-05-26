<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class AbsensiController extends Controller
{
    // Tampilkan semua data absensi
public function getStudentsByClass(Request $request)
{
    $kelas = $request->query('kelas');
    if (!$kelas) {
        return response()->json(['message' => 'Kelas wajib disertakan'], 400);
    }

    $students = User::where('kelas', $kelas)
                    ->select('nisn', 'nama', 'tanggal_lahir', 'jenis_kelamin')
                    ->get();

    return response()->json($students);
}

public function updateStatusTidakHadirJikaWaktuSelesai($kelas, $tanggal)
{
    // Ambil data absensi yang statusnya bukan 'hadir' (misal kosong atau belum diupdate)
    // atau kamu bisa tentukan kondisi lain sesuai kebutuhan
    $absensiBelumHadir = Absensi::where('kelas', $kelas)
        ->where('tanggal', $tanggal)
        ->where('status', '!=', 'hadir') // atau kondisi lain jika status masih kosong, dll.
        ->get();

    $now = date('H:i'); // jam saat ini (format 24 jam, misal "15:30")

    // Ambil jam selesai dari absensi yang sudah ada, kalau ada banyak, ambil salah satu (asumsi sama)
    $jamSelesai = Absensi::where('kelas', $kelas)
        ->where('tanggal', $tanggal)
        ->value('selesai');

    if (!$jamSelesai) {
        // Jika belum ada jam selesai, return
        return response()->json(['message' => 'Jam selesai belum diatur.'], 400);
    }

    if ($now >= $jamSelesai) {
        // Update semua yang belum hadir jadi 'tidak hadir'
        Absensi::where('kelas', $kelas)
            ->where('tanggal', $tanggal)
            ->where('status', '!=', 'hadir')
            ->update(['status' => 'tidak hadir']);

        return response()->json(['message' => 'Status absensi otomatis diupdate menjadi tidak hadir.']);
    }

    return response()->json(['message' => 'Belum waktunya update status.']);
}

public function inputAbsensi(Request $request)
{
    $request->validate([
        'kelas' => 'required|string',
        'tanggal' => 'required|date',
        'hari' => 'required|string',
        'mulai' => 'required|string',
        'selesai' => 'required|string',
        'absensi' => 'required|array',
        'absensi.*.nisn' => 'required|string',
        'absensi.*.status' => 'required|string',
        'absensi.*.waktu_absen' => 'required|string',
    ]);

    $kelas = $request->kelas;
    $tanggal = $request->tanggal;
    $hari = $request->hari;
    $mulai = $request->mulai;
    $selesai = $request->selesai;
    $absensiData = $request->absensi;

    foreach ($absensiData as $item) {
        // Cari user berdasarkan nisn
        $user = User::where('nisn', $item['nisn'])->first();

        if (!$user) {
            // Skip jika user tidak ditemukan
            continue;
        }

        // Gunakan updateOrCreate untuk mencegah duplikasi absensi
        Absensi::updateOrCreate(
            [
                'user_id' => $user->id,
                'kelas' => $kelas,
                'tanggal' => $tanggal,
            ],
            [
                'hari' => $hari,
                'mulai' => $mulai,
                'selesai' => $selesai,
                'status' => $item['status'],
                'waktu_absen' => $item['waktu_absen'],
            ]
        );
    }

    $this->updateStatusTidakHadirJikaWaktuSelesai($kelas, $tanggal);


    return response()->json([
        'message' => 'Absensi berhasil disimpan atau diperbarui.'
    ], 200);
}


public function getAbsensi(Request $request)
{
    $request->validate([
        'kelas' => 'required|string',
        'tanggal' => 'required|date',
    ]);

    $kelas = $request->kelas;
    $tanggal = $request->tanggal;

    // Ambil data absensi berdasarkan kelas dan tanggal
    $absensi = Absensi::with('user')
        ->where('kelas', $kelas)
        ->where('tanggal', $tanggal)
        ->get();

    if ($absensi->isEmpty()) {
        return response()->json([
            'message' => 'Data absensi tidak ditemukan',
            'kelas' => $kelas,
            'hari' => null,
            'mulai' => null,
            'selesai' => null,
            'data' => [],
        ]);
    }

    // Ambil data hari, mulai, selesai dari record pertama
    $first = $absensi->first();

    $result = $absensi->map(function ($item) {
        return [
            'nama' => $item->user->nama,
            'nisn' => $item->user->nisn,
            'status' => $item->status,
            'waktu_absen' => $item->waktu_absen,
            'tanggal' => $item->tanggal,
            // jangan ulang hari, mulai, selesai di tiap record
        ];
    });

    return response()->json([
        'message' => 'Data absensi berhasil diambil',
        'kelas' => $kelas,
        'hari' => $first->hari,
        'mulai' => $first->mulai,
        'selesai' => $first->selesai,
        'data' => $result,
    ]);
}


}
