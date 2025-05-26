<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use App\Http\Controllers\AktivitasKegiatanController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\PiketController;
use App\Http\Controllers\StudyTourController;


Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

Route::post('addUser', [AuthController::class, 'register']);
Route::get('/users', [AuthController::class, 'index']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:api')->get('/user', [UserController::class, 'getUserData']);

Route::apiResource('absensi', AbsensiController::class);

Route::post('/ekskul', [EkskulController::class, 'store']);

Route::apiResource('detail-ekskul', DetailEkskulController::class);

Route::get('/jumlahUser', [UserController::class, 'getUserCounts']);

Route::get('/siswa', [UserController::class, 'getAllSiswa']);
Route::get('/guru', [UserController::class, 'getAllGuru']);
Route::get('/total-user', [UserController::class, 'getUsersWithTotal']);

Route::middleware('auth:api')->get('/user-profile', [UserController::class, 'getProfile']);

Route::middleware('auth:api')->put('/update-profile', [UserController::class, 'updateProfile']);

Route::get('/jumlah-siswa', [UserController::class, 'siswaGender']);
Route::get('/jumlah-guru', [UserController::class, 'guruGender']);

Route::get('/siswa-kelas', [AbsensiController::class, 'getStudentsByClass']);  // ?kelas=X-A

Route::post('/input-absensi', [AbsensiController::class, 'inputAbsensi']);              // Simpan absensi

Route::get('/absensi', [AbsensiController::class, 'getAbsensi']);
Route::get('/absensi-piket', [PiketController::class, 'getPiket']);
Route::post('/input-piket', [PiketController::class, 'inputPiket']);              // Simpan absensi
Route::get('/kontribusi-piket', [PiketController::class, 'rekapKontribusiBulanan']);
Route::get('/absensi-tour', [StudyTourController::class, 'getStudyTour']);
Route::post('/input-tour', [StudyTourController::class, 'inputStudyTour']); 
Route::get('/kehadiran-chart', [AbsensiController::class, 'getChartData']);
Route::get('/statistik-hari-ini', [AbsensiController::class, 'getAbsensiStatistikHariIni']);
Route::get('/statistik-bulanan', [AbsensiController::class, 'getAbsensiStatistikBulanan']);
Route::get('/list-absensi-siswa', [AbsensiController::class, 'listAbsensi']);
Route::get('/detail-siswa', [UserController::class, 'detailSiswa']);
Route::get('/absensi-detail', [AbsensiController::class, 'getAbsensiByNisn']);

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
