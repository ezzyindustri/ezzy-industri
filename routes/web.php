<?php

use App\Livewire\Karyawan\KaryawanDashboard;
use App\Livewire\Karyawan\Production\StartProduction;
use App\Livewire\Karyawan\Production\ProductionStatus;
use App\Livewire\Karyawan\Production\ProductionChecksheet;
use App\Livewire\Login;
use App\Livewire\Manajerial\ManajerialDashboard;
use App\Livewire\Manajerial\Production\ProblemApproval;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Karyawan\Production\CheckProductionStatus;
use App\Livewire\Karyawan\QualityCheck\QualityCheckForm;
use App\Livewire\Manajerial\Manajemen\ProductManagement;
use App\Livewire\Production\ProductionReport;
use App\Livewire\Manajerial\Sop\SopApproval;
use App\Http\Controllers\SopPdfController;
use App\Http\Controllers\KaryawanDetailPdfController;
use App\Livewire\Manajerial\OeeDashboard;
use App\Livewire\Manajerial\OeeDetail;
use App\Http\Controllers\OeePdfController;




Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', Login::class)->name('login')->middleware('guest');

Route::get('/production/{productionId}/report', ProductionReport::class)->name('production.report');

// Manajerial Routes
// Inside manajerial routes group
Route::prefix('manajerial')->middleware(['auth', 'role:manajerial'])->group(function () {
    Route::get('/dashboard', \App\Livewire\Manajerial\Dashboard::class)->name('manajerial.dashboard');    
    Route::get('/production/problems', ProblemApproval::class)->name('manajerial.production.problems');
    Route::get('/machines', \App\Livewire\Manajerial\Manajemen\MachineManagement::class)->name('manajerial.machines');
    Route::get('/shifts', \App\Livewire\Manajerial\Manajemen\ShiftManagement::class)->name('manajerial.shifts');
    Route::get('/maintenance-tasks', \App\Livewire\Manajerial\Manajemen\MaintenanceTaskManagement::class)->name('manajerial.maintenance-tasks');
    Route::get('/sop', \App\Livewire\Manajerial\Sop\SopIndex::class)->name('manajerial.sop');
    Route::get('/sop/{id}', \App\Livewire\Manajerial\Sop\SopDetail::class)->name('manajerial.sop.detail');
    Route::get('/products', \App\Livewire\Manajerial\Manajemen\ProductManagement::class)->name('manajerial.products');
    Route::get('/manajerial/sop/approval', SopApproval::class)->name('manajerial.sop.approval');
    Route::get('/manajerial/sop/{id}/pdf', [SopPdfController::class, 'generate'])
    ->name('manajerial.sop.pdf')
    ->middleware(['auth', 'role:manajerial']);
    // Fix: Pindahkan route PDF ke dalam group manajerial dan perbaiki path-nya
    Route::get('/karyawan-report', App\Livewire\Manajerial\KaryawanReport::class)
        ->name('manajerial.karyawan-report');
    
    // Add these PDF routes
    Route::get('/karyawan-report/pdf/{dateFrom}/{dateTo}', [KaryawanDetailPdfController::class, 'generateReport'])
        ->name('manajerial.karyawan.report.pdf');
    Route::get('/karyawan/{userId}/pdf/{dateFrom}/{dateTo}', [KaryawanDetailPdfController::class, 'generate'])
        ->name('manajerial.karyawan.detail.pdf');
    Route::get('/users', \App\Livewire\Manajerial\Manajemen\UserManagement::class)->name('manajerial.users');
    Route::get('/karyawan/detail-pdf/{userId}/{dateFrom}/{dateTo}', [KaryawanDetailPdfController::class, 'generate'])
    ->name('karyawan.detail.pdf');
    Route::get('/oee-dashboard', \App\Livewire\Manajerial\OeeDashboard::class)
    ->name('manajerial.oee-dashboard');
    Route::get('/manajerial/oee/{machineId}/detail', \App\Livewire\Manajerial\OeeDetail::class)
    ->name('manajerial.oee.detail');
    
    // Add these OEE PDF routes
    Route::get('/oee/dashboard/pdf', [OeePdfController::class, 'generateDashboardPdf'])
        ->name('manajerial.oee.dashboard.pdf');
    Route::get('/oee/{machineId}/detail/pdf', [OeePdfController::class, 'generateDetailPdf'])
        ->name('manajerial.oee.detail.pdf');
});


// Karyawan Routes
Route::prefix('karyawan')->middleware(['auth', 'role:karyawan'])->group(function () {
    Route::get('/dashboard', \App\Livewire\Karyawan\Dashboard::class)->name('karyawan.dashboard');
    Route::get('/production/status', ProductionStatus::class)->name('production.status');
    Route::get('/production/start', StartProduction::class)->name('production.start');
    Route::get('/production/checksheet/{machineId}/{shiftId}', \App\Livewire\Components\ChecksheetTable::class)
        ->name('production.checksheet')
        ->where(['machineId' => '[0-9]+', 'shiftId' => '[0-9]+']);
    Route::get('/karyawan/production/finish/{productionId}', \App\Livewire\Karyawan\Production\FinishProduction::class)
        ->name('production.finish')
        ->middleware(['auth', 'role:karyawan']);
        Route::get('/production/{productionId}/quality-check', QualityCheckForm::class)->name('production.quality-check');
});



Route::post('/logout', function() {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/login');
})->name('logout');