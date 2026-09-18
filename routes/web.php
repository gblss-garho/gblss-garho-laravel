<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\FaceEnrollController;
use App\Http\Controllers\TeacherAttendanceController;
use App\Http\Controllers\TeacherQrScanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::get('/portal', function () {
    return view('pages.portal-select');
})->name('portal.select');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', fn () => view('dashboards.admin'))->name('admin.dashboard');
});

Route::middleware(['auth', 'role:teacher'])->group(function () {
    Route::get('/teacher/dashboard', fn () => view('dashboards.teacher'))->name('teacher.dashboard');
    Route::get('/teacher/attendance', [TeacherAttendanceController::class, 'index'])->name('teacher.attendance.index');
    Route::post('/teacher/attendance/checkin', [TeacherAttendanceController::class, 'checkIn'])->name('teacher.attendance.checkin');
    Route::post('/teacher/attendance/checkout', [TeacherAttendanceController::class, 'checkOut'])->name('teacher.attendance.checkout');
    Route::get('/teacher/face-enroll', [FaceEnrollController::class, 'show'])->name('teacher.face.enroll');
    Route::post('/teacher/face-enroll', [FaceEnrollController::class, 'store'])->name('teacher.face.enroll.store');
    Route::get('/teacher/qr-scan', [TeacherQrScanController::class, 'show'])->name('teacher.qr.scan');
    Route::post('/teacher/qr-scan', [TeacherQrScanController::class, 'store'])->name('teacher.qr.scan.store');
});

Route::middleware(['auth', 'role:parent'])->group(function () {
    Route::get('/parent/dashboard', fn () => view('dashboards.parent'))->name('parent.dashboard');
});

Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('/student/dashboard', fn () => view('dashboards.student'))->name('student.dashboard');
});
