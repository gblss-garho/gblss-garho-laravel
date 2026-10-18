<?php

use App\Http\Controllers\AdminLeavingCertificateController;
use App\Http\Controllers\AdminPromotionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\FaceEnrollController;
use App\Http\Controllers\TeacherAttendanceController;
use App\Http\Controllers\TeacherQrScanController;
use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\PublicHomeController::class, 'index'])->name('home');

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
Route::get('/teacher/absentees', [\App\Http\Controllers\TeacherAbsenteesController::class, 'index'])->name('teacher.absentees');
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('students', \App\Http\Controllers\AdminStudentController::class)->except(['show']);
    Route::get('results', [\App\Http\Controllers\AdminResultController::class, 'index'])->name('results.index');
});

Route::middleware(['auth', 'role:admin'])->post('/admin/results/toggle', [\App\Http\Controllers\AdminResultPublishController::class, 'toggle'])->name('admin.results.toggle');

Route::get('/parent-lookup', [\App\Http\Controllers\PublicLookupController::class, 'show'])->name('public.lookup');
Route::post('/parent-lookup', [\App\Http\Controllers\PublicLookupController::class, 'check'])->middleware('throttle:30,1')->name('public.lookup.check');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('teachers', \App\Http\Controllers\AdminTeacherController::class)->except(['show']);
});

Route::middleware(['auth', 'role:admin'])->prefix('admin/content')->name('admin.content.')->group(function () {
    Route::get('/', [\App\Http\Controllers\AdminContentController::class, 'index'])->name('index');
    Route::post('/notice', [\App\Http\Controllers\AdminContentController::class, 'addNotice'])->name('notice.add');
    Route::delete('/notice/{id}', [\App\Http\Controllers\AdminContentController::class, 'deleteNotice'])->name('notice.delete');
    Route::post('/event', [\App\Http\Controllers\AdminContentController::class, 'addEvent'])->name('event.add');
    Route::delete('/event/{id}', [\App\Http\Controllers\AdminContentController::class, 'deleteEvent'])->name('event.delete');
});

Route::middleware(['auth', 'role:teacher'])->prefix('teacher/homework')->name('teacher.homework.')->group(function () {
    Route::get('/', [\App\Http\Controllers\TeacherHomeworkController::class, 'index'])->name('index');
    Route::post('/', [\App\Http\Controllers\TeacherHomeworkController::class, 'store'])->name('store');
    Route::delete('/{id}', [\App\Http\Controllers\TeacherHomeworkController::class, 'destroy'])->name('destroy');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin/settings')->name('admin.settings.')->group(function () {
    Route::get('/', [\App\Http\Controllers\AdminSettingsController::class, 'edit'])->name('edit');
    Route::post('/', [\App\Http\Controllers\AdminSettingsController::class, 'update'])->name('update');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin/settings')->name('admin.settings.')->group(function () {
    Route::get('/', [\App\Http\Controllers\AdminSettingsController::class, 'edit'])->name('edit');
    Route::post('/', [\App\Http\Controllers\AdminSettingsController::class, 'update'])->name('update');
});

Route::middleware(['auth', 'role:teacher'])->prefix('teacher/leave')->name('teacher.leave.')->group(function () {
    Route::get('/', [\App\Http\Controllers\TeacherLeaveController::class, 'index'])->name('index');
    Route::post('/', [\App\Http\Controllers\TeacherLeaveController::class, 'store'])->name('store');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin/leave')->name('admin.leave.')->group(function () {
    Route::get('/', [\App\Http\Controllers\AdminLeaveController::class, 'index'])->name('index');
    Route::put('/{id}', [\App\Http\Controllers\AdminLeaveController::class, 'update'])->name('update');
});

Route::get('/gallery', [\App\Http\Controllers\PublicPagesController::class, 'gallery'])->name('public.gallery');
Route::get('/textbooks', [\App\Http\Controllers\PublicPagesController::class, 'textbooks'])->name('public.textbooks');
Route::get('/feedback', [\App\Http\Controllers\PublicPagesController::class, 'feedbackForm'])->name('public.feedback');
Route::post('/feedback', [\App\Http\Controllers\PublicPagesController::class, 'feedbackStore'])->middleware('throttle:5,10')->name('public.feedback.store');

Route::middleware(['auth', 'role:admin'])->prefix('admin/media')->name('admin.media.')->group(function () {
    Route::get('/', [\App\Http\Controllers\AdminMediaController::class, 'index'])->name('index');
    Route::post('/gallery', [\App\Http\Controllers\AdminMediaController::class, 'galleryAdd'])->name('gallery.add');
    Route::delete('/gallery/{id}', [\App\Http\Controllers\AdminMediaController::class, 'galleryDelete'])->name('gallery.delete');
    Route::post('/textbook', [\App\Http\Controllers\AdminMediaController::class, 'textbookAdd'])->name('textbook.add');
    Route::delete('/textbook/{id}', [\App\Http\Controllers\AdminMediaController::class, 'textbookDelete'])->name('textbook.delete');
    Route::delete('/feedback/{id}', [\App\Http\Controllers\AdminMediaController::class, 'feedbackDelete'])->name('feedback.delete');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin/bookbank')->name('admin.bookbank.')->group(function () {
    Route::get('/', [\App\Http\Controllers\AdminBookBankController::class, 'index'])->name('index');
    Route::post('/catalog', [\App\Http\Controllers\AdminBookBankController::class, 'catalogAdd'])->name('catalog.add');
    Route::post('/catalog/{id}/qty', [\App\Http\Controllers\AdminBookBankController::class, 'catalogQty'])->name('catalog.qty');
    Route::delete('/catalog/{id}', [\App\Http\Controllers\AdminBookBankController::class, 'catalogDelete'])->name('catalog.delete');
    Route::post('/issue', [\App\Http\Controllers\AdminBookBankController::class, 'issue'])->name('issue');
    Route::post('/issues/{id}/return', [\App\Http\Controllers\AdminBookBankController::class, 'giveBack'])->name('return');
});
