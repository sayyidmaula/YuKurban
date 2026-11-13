<?php

use App\Services\InvoiceService;
use App\Services\PlanService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('invoices:expire', function (InvoiceService $invoiceService) {
    $count = $invoiceService->expirePendingInvoices();
    $this->info("Expired {$count} pending invoices.");
})->purpose('Expire pending invoices past expiration time');

Artisan::command('autodebet:generate', function (PlanService $planService) {
    $today = (int) now()->day;
    $count = $planService->generateDueAutodebetInvoices($today);
    $this->info("Generated {$count} autodebet invoices for day {$today}.");
})->purpose('Generate daily autodebet invoices for due savings plans');

Schedule::command('invoices:expire')->everyMinute();
Schedule::command('autodebet:generate')->dailyAt('06:00');
