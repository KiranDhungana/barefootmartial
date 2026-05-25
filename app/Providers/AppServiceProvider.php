<?php

namespace App\Providers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Observers\InvoiceObserver;
use App\Observers\PaymentObserver;
use App\Observers\StudentObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('path.public', function () {
            $publicHtml = base_path('public_html');

            return file_exists($publicHtml) ? $publicHtml : base_path('public');
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Student::observe(StudentObserver::class);
        Invoice::observe(InvoiceObserver::class);
        Payment::observe(PaymentObserver::class);
    }
}
