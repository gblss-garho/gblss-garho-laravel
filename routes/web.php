<?php

use App\Http\Controllers\AdminLeavingCertificateController;
use App\Http\Controllers\AdminPromotionController;
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
    Route::get('/admin/promotions', [AdminPromotionController::class, 'history'])->name('admin.promotions.history');
    Route::get('/admin/promotions/{batch}/review', [AdminPromotionController::class, 'review'])->name('admin.promotions.review');
    Route::post('/admin/promotions/{batch}/undo', [AdminPromotionController::class, 'undo'])->name('admin.promotions.undo');
    Route::post('/admin/promotions/{batch}/undo/{student}', [AdminPromotionController::class, 'undoStudent'])->name('admin.promotions.undoStudent');
    Route::get('/admin/leaving-certificates', [AdminLeavingCertificateController::class, 'index'])->name('admin.leaving-certificates.index');
    Route::get('/admin/leaving-certificates/create/{student}', [AdminLeavingCertificateController::class, 'create'])->name('admin.leaving-certificates.create');
    Route::post('/admin/leaving-certificates/{student}', [AdminLeavingCertificateController::class, 'store'])->name('admin.leaving-certificates.store');
    Route::get('/admin/leaving-certificates/{certificate}/pdf', [AdminLeavingCertificateController::class, 'pdf'])->name('admin.leaving-certificates.pdf');
    Route::get('/admin/id-cards', [\App\Http\Controllers\AdminIdCardController::class, 'index'])->name('admin.id-cards.index');
    Route::get('/admin/id-cards/{student}/pdf', [\App\Http\Controllers\AdminIdCardController::class, 'pdf'])->name('admin.id-cards.pdf');
    Route::get('/admin/staff-id-cards', [\App\Http\Controllers\AdminStaffIdCardController::class, 'index'])->name('admin.staff-id-cards.index');
    Route::get('/admin/staff-id-cards/{teacher}/pdf', [\App\Http\Controllers\AdminStaffIdCardController::class, 'pdf'])->name('admin.staff-id-cards.pdf');
    Route::get('/admin/admit-cards', [\App\Http\Controllers\AdminAdmitCardController::class, 'index'])->name('admin.admit-cards.index');
    Route::get('/admin/admit-cards/{student}/pdf', [\App\Http\Controllers\AdminAdmitCardController::class, 'pdf'])->name('admin.admit-cards.pdf');
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
