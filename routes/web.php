<?php

use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Erp\AttendanceController;
use App\Http\Controllers\Erp\DashboardController;
use App\Http\Controllers\Erp\InvoiceController;
use App\Http\Controllers\Erp\ReportController;
use App\Http\Controllers\Erp\SalaryController;
use App\Http\Controllers\Erp\StudentController;
use App\Http\Controllers\Erp\TrainerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Secondcontroller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::middleware('guest')->group(function () {
    Route::get('login/two-factor', [TwoFactorController::class, 'showChallenge'])->name('two-factor.challenge');
    Route::post('login/two-factor', [TwoFactorController::class, 'challenge'])->name('two-factor.verify');
});

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/admin/home', [HomeController::class, 'adminhome'])->name('admin-home')->middleware('checkadmin');
Route::get('/admin/reg', [HomeController::class, 'register_user'])->name('register_user')->middleware('checkadmin');
Route::post('/admin/reg', [HomeController::class, 'register'])->name('register')->middleware('checkadmin');
Route::post('/admin-del/{id}', [HomeController::class, 'delete'])->name('user.destroy')->middleware('checkadmin');
Route::get('/admin-update/{id}', [HomeController::class, 'update'])->name('user.update')->middleware('checkadmin');
Route::post('/admin-update/{id}', [HomeController::class, 'update_info'])->name('user.update_info')->middleware('checkadmin');
Route::get('/add-notice', [HomeController::class, 'add_noticepage'])->name('add_notice')->middleware('checkadmin');
Route::post('/add-notice', [HomeController::class, 'store'])->name('store')->middleware('checkadmin');
Route::get('/delete-notice', [HomeController::class, 'del_notice'])->name('del_notice')->middleware('checkadmin');
Route::post('/delete-notice/{id}', [HomeController::class, 'delete_notice'])->name('delete.notice')->middleware('checkadmin');

Route::middleware(['auth'])->group(function () {
    Route::get('/erp/account/two-factor', [TwoFactorController::class, 'showSetup'])->name('two-factor.setup');
    Route::post('/erp/account/two-factor/generate', [TwoFactorController::class, 'generateSecret'])->name('two-factor.generate');
    Route::post('/erp/account/two-factor/confirm', [TwoFactorController::class, 'confirmSetup'])->name('two-factor.confirm');
    Route::post('/erp/account/two-factor/disable', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
});

Route::middleware(['auth', 'erp'])->prefix('erp')->name('erp.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('students/{student}/id-card.pdf', [StudentController::class, 'idCardPdf'])->name('students.id-card');
    Route::resource('students', StudentController::class);

    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance/day', [AttendanceController::class, 'saveDay'])->name('attendance.day');
    Route::get('attendance/scan/{token}', [AttendanceController::class, 'scan'])->name('attendance.scan');

    Route::middleware('admin')->group(function () {
        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
        Route::post('invoices/{invoice}/paid', [InvoiceController::class, 'markPaid'])->name('invoices.paid');
        Route::post('invoices/{invoice}/pending', [InvoiceController::class, 'markPending'])->name('invoices.pending');
        Route::resource('invoices', InvoiceController::class)->only(['index', 'create', 'store', 'show']);

        Route::resource('trainers', TrainerController::class)->except(['show']);

        Route::get('salary', [SalaryController::class, 'index'])->name('salary.index');
        Route::post('salary/generate', [SalaryController::class, 'generate'])->name('salary.generate');
        Route::get('salary/pdf', [SalaryController::class, 'pdf'])->name('salary.pdf');

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export.csv', [ReportController::class, 'exportCsv'])->name('reports.export');
        Route::get('reports/export.pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
    });
});

Route::get('/contact', [Secondcontroller::class, 'contactus'])->name('contact');
Route::post('/contact', [Secondcontroller::class, 'mailsend'])->name('send_mail');
Route::get('/notice-home', [Secondcontroller::class, 'notice_home'])->name('notice_home');
Route::get('/notice-home/{id}', [Secondcontroller::class, 'notice_main'])->name('notice_main');
Route::get('/about-us', function () {
    return view('aboutus');
});
Route::get('/gallary', function () {
    return view('galary');
});
Route::get('command', function () {
    $targetFolder = $_SERVER['DOCUMENT_ROOT'].'/storage/app/public';
    $linkFolder = $_SERVER['DOCUMENT_ROOT'].'/public/storage';
    symlink($targetFolder, $linkFolder);
    echo 'Symlink process successfully completed';
});
