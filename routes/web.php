<?php

use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Erp\AttendanceController;
use App\Http\Controllers\Erp\AuditLogController;
use App\Http\Controllers\Erp\DashboardController;
use App\Http\Controllers\Erp\ErpUserController;
use App\Http\Controllers\Erp\FeeController;
use App\Http\Controllers\Erp\InventoryController;
use App\Http\Controllers\Erp\InvoiceController;
use App\Http\Controllers\Erp\ReportController;
use App\Http\Controllers\Erp\SalaryController;
use App\Http\Controllers\Erp\StudentController;
use App\Http\Controllers\Erp\StudentImportController;
use App\Http\Controllers\Erp\TrainerController;
use App\Http\Controllers\Erp\BeltController;
use App\Http\Controllers\Erp\BranchReportController;
use App\Http\Controllers\Erp\ExpenseController;
use App\Http\Controllers\Erp\BranchController;
use App\Http\Controllers\Erp\ComplianceController;
use App\Http\Controllers\Erp\EventController;
use App\Http\Controllers\Erp\HqDashboardController;
use App\Http\Controllers\Erp\InventoryReportController;
use App\Http\Controllers\Erp\NotificationController;
use App\Http\Controllers\Erp\OnlineRegistrationController;
use App\Http\Controllers\Erp\ParentAccountController;
use App\Http\Controllers\Erp\ScheduleController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Parent\ParentPortalController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\QrVerifyController;
use App\Http\Controllers\Secondcontroller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/branches', [PublicSiteController::class, 'branches'])->name('public.branches');
Route::get('/register-online', [PublicSiteController::class, 'registerForm'])->name('public.register');
Route::post('/register-online', [PublicSiteController::class, 'registerStore'])->name('public.register.store');
Route::get('/events', [PublicSiteController::class, 'events'])->name('public.events');
Route::get('/coaches', [PublicSiteController::class, 'coaches'])->name('public.coaches');
Route::get('/notices', [PublicSiteController::class, 'notices'])->name('public.notices');

Route::get('/verify/{token}', [QrVerifyController::class, 'show'])->name('verify.student');

Auth::routes();

Route::middleware('guest')->group(function () {
    Route::get('login/two-factor', [TwoFactorController::class, 'showChallenge'])->name('two-factor.challenge');
    Route::post('login/two-factor', [TwoFactorController::class, 'challenge'])->name('two-factor.verify');
});

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::middleware(['auth', 'parent'])->prefix('parent')->name('parent.')->group(function () {
    Route::get('/', [ParentPortalController::class, 'dashboard'])->name('dashboard');
});

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
    Route::get('hq', [HqDashboardController::class, 'index'])->name('hq.dashboard');

    Route::get('belts', [BeltController::class, 'index'])->name('belts.index');
    Route::get('belts/students/{student}', [BeltController::class, 'promoteForm'])->name('belts.promote');
    Route::post('belts/students/{student}', [BeltController::class, 'promote'])->name('belts.promote.store');
    Route::get('belts/students/{student}/certificate/{promotion}.pdf', [BeltController::class, 'certificatePdf'])
        ->name('belts.certificate');

    Route::get('students/import', [StudentImportController::class, 'index'])->name('students.import');
    Route::get('students/import/template', [StudentImportController::class, 'template'])->name('students.import.template');
    Route::post('students/import', [StudentImportController::class, 'store'])->name('students.import.store');
    Route::post('students/import/manual', [StudentImportController::class, 'storeManual'])->name('students.import.manual');
    Route::get('students/{student}/id-card.pdf', [StudentController::class, 'idCardPdf'])->name('students.id-card');
    Route::post('students/{student}/mark-official', [StudentController::class, 'markOfficial'])->name('students.mark-official');
    Route::resource('students', StudentController::class);

    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit.index');

    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('attendance/bulk', [AttendanceController::class, 'bulk'])->name('attendance.bulk');
    Route::post('attendance/bulk', [AttendanceController::class, 'bulkSave'])->name('attendance.bulk.store');
    Route::post('attendance/day', [AttendanceController::class, 'saveDay'])->name('attendance.day');
    Route::get('attendance/scan/{token}', [AttendanceController::class, 'scan'])->name('attendance.scan');

    Route::get('branches', [BranchController::class, 'index'])->name('branches.index');
    Route::get('branches/{branch}', [BranchController::class, 'show'])->name('branches.show');
    Route::get('schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::get('schedules/create', [ScheduleController::class, 'create'])->name('schedules.create');
    Route::post('schedules', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::get('schedules/{schedule}/edit', [ScheduleController::class, 'edit'])->name('schedules.edit');
    Route::put('schedules/{schedule}', [ScheduleController::class, 'update'])->name('schedules.update');
    Route::delete('schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');

    Route::get('events', [EventController::class, 'index'])->name('events.index');
    Route::get('events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('events', [EventController::class, 'store'])->name('events.store');
    Route::get('events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::get('events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::post('events/{event}/register', [EventController::class, 'registerStudent'])->name('events.register');

    Route::get('online-registrations', [OnlineRegistrationController::class, 'index'])->name('online-registrations.index');
    Route::post('online-registrations/{onlineRegistration}/status', [OnlineRegistrationController::class, 'updateStatus'])->name('online-registrations.status');
    Route::post('online-registrations/{onlineRegistration}/convert', [OnlineRegistrationController::class, 'convert'])->name('online-registrations.convert');

    Route::get('students/{student}/parent-account', [ParentAccountController::class, 'create'])->name('parents.create');
    Route::post('students/{student}/parent-account', [ParentAccountController::class, 'store'])->name('parents.store');

    Route::middleware('finance')->group(function () {
        Route::get('fees', [FeeController::class, 'index'])->name('fees.index');
        Route::get('fees/reminders', [FeeController::class, 'reminders'])->name('fees.reminders');

        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('inventory/stock', [InventoryController::class, 'adjustStock'])->name('inventory.stock');
        Route::get('inventory/transfer', [InventoryController::class, 'transferForm'])->name('inventory.transfer');
        Route::post('inventory/transfer', [InventoryController::class, 'transfer'])->name('inventory.transfer.store');

        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
        Route::get('invoices/{invoice}/payment-slip.pdf', [InvoiceController::class, 'paymentSlipPdf'])->name('invoices.payment-slip');
        Route::get('invoices/{invoice}/payments/{payment}/receipt.pdf', [InvoiceController::class, 'receiptPdf'])->name('invoices.receipt');

        Route::get('inventory/report', [InventoryReportController::class, 'index'])->name('inventory.report');
        Route::get('inventory/report/export.csv', [InventoryReportController::class, 'exportCsv'])->name('inventory.report.export');

        Route::get('compliance', [ComplianceController::class, 'index'])->name('compliance.index');
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/send', [NotificationController::class, 'send'])->name('notifications.send');
        Route::post('notifications/invoice/{invoice}', [NotificationController::class, 'sendInvoiceReminder'])->name('notifications.invoice');
        Route::post('invoices/{invoice}/payments', [InvoiceController::class, 'storePayment'])->name('invoices.payments.store');
        Route::post('invoices/{invoice}/late-fee', [InvoiceController::class, 'applyLateFee'])->name('invoices.late-fee');
        Route::post('invoices/{invoice}/paid', [InvoiceController::class, 'markPaid'])->name('invoices.paid');
        Route::post('invoices/{invoice}/pending', [InvoiceController::class, 'markPending'])->name('invoices.pending');
        Route::resource('invoices', InvoiceController::class)->only(['index', 'create', 'store', 'show']);

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export.csv', [ReportController::class, 'exportCsv'])->name('reports.export');
        Route::get('reports/export.pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');

        Route::get('branch-reports', [BranchReportController::class, 'index'])->name('branch-reports.index');
        Route::get('branch-reports/pdf', [BranchReportController::class, 'exportPdf'])->name('branch-reports.pdf');

        Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    });

    Route::middleware('super_admin')->group(function () {
        Route::get('branches/create', [BranchController::class, 'create'])->name('branches.create');
        Route::post('branches', [BranchController::class, 'store'])->name('branches.store');
        Route::get('branches/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
        Route::put('branches/{branch}', [BranchController::class, 'update'])->name('branches.update');

        Route::resource('users', ErpUserController::class)->only(['index', 'create', 'store']);
        Route::resource('trainers', TrainerController::class)->except(['show']);
        Route::get('salary', [SalaryController::class, 'index'])->name('salary.index');
        Route::post('salary/generate', [SalaryController::class, 'generate'])->name('salary.generate');
        Route::get('salary/pdf', [SalaryController::class, 'pdf'])->name('salary.pdf');
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
