<?php

use App\Http\Controllers\KatalogController;
use App\Http\Controllers\Penabung\BerandaController;
use App\Http\Controllers\Penabung\InvoiceController;
use App\Http\Controllers\Penabung\PlanController;
use App\Http\Controllers\Peternak\HewanController;
use App\Http\Controllers\Peternak\ProfilController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'landing'])->name('landing');
Route::get('/faq', [PublicController::class, 'faq'])->name('faq');
Route::get('/ketentuan', [PublicController::class, 'ketentuan'])->name('ketentuan');
Route::get('/hubungi', [PublicController::class, 'hubungi'])->name('hubungi');
Route::post('/hubungi', [PublicController::class, 'storeContact'])->name('hubungi.store');

Route::get('/katalog', [KatalogController::class, 'index'])->name('katalog.index');
Route::get('/katalog/{animal}', [KatalogController::class, 'show'])->name('katalog.show');

/*
|--------------------------------------------------------------------------
| Dashboard Redirect Route
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function (Request $request) {
    $user = $request->user();
    if ($user->isPeternak()) {
        return redirect()->route('peternak.beranda');
    }
    if ($user->isAdmin()) {
        return redirect('/admin');
    }

    return redirect()->route('beranda');
})->middleware(['auth'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Penabung Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:penabung'])->group(function () {
    Route::get('/beranda', [BerandaController::class, 'index'])->name('beranda');

    Route::get('/rencana', [PlanController::class, 'index'])->name('rencana.index');
    Route::get('/rencana/buat', [PlanController::class, 'create'])->name('rencana.create');
    Route::post('/rencana', [PlanController::class, 'store'])->name('rencana.store');
    Route::get('/rencana/{plan}', [PlanController::class, 'show'])->name('rencana.show');
    Route::get('/rencana/{plan}/setor', [PlanController::class, 'setor'])->name('rencana.setor');
    Route::post('/rencana/{plan}/setor', [PlanController::class, 'processSetor'])->name('rencana.setor.process');
    Route::get('/rencana/{plan}/tarik', [PlanController::class, 'tarik'])->name('rencana.tarik');
    Route::post('/rencana/{plan}/tarik', [PlanController::class, 'processTarik'])->name('rencana.tarik.process');
    Route::post('/rencana/{plan}/autodebet', [PlanController::class, 'updateAutodebet'])->name('rencana.autodebet');

    Route::get('/tagihan/{invoice}', [InvoiceController::class, 'show'])->name('tagihan.show');
    Route::post('/tagihan/{invoice}/simulasi-berhasil', [InvoiceController::class, 'simulatePay'])->name('tagihan.simulasi.berhasil');
    Route::post('/tagihan/{invoice}/simulasi-gagal', [InvoiceController::class, 'simulateFail'])->name('tagihan.simulasi.gagal');

    Route::get('/akun', function (Request $request) {
        return Inertia::render('Penabung/Akun');
    })->name('akun');
});

/*
|--------------------------------------------------------------------------
| Peternak Routes
|--------------------------------------------------------------------------
*/
Route::prefix('peternak')->name('peternak.')->middleware(['auth', 'role:peternak'])->group(function () {
    Route::get('/beranda', function () {
        return Inertia::render('Peternak/Beranda');
    })->name('beranda');

    Route::get('/profil', [ProfilController::class, 'show'])->name('profil');
    Route::post('/profil', [ProfilController::class, 'update'])->name('profil.update');

    Route::get('/hewan', [HewanController::class, 'index'])->name('hewan.index');
    Route::get('/hewan/buat', [HewanController::class, 'create'])->name('hewan.create');
    Route::post('/hewan', [HewanController::class, 'store'])->name('hewan.store');
    Route::get('/hewan/{animal}/edit', [HewanController::class, 'edit'])->name('hewan.edit');
    Route::match(['put', 'patch'], '/hewan/{animal}', [HewanController::class, 'update'])->name('hewan.update');

    Route::get('/bantuan', function () {
        return Inertia::render('Peternak/Bantuan');
    })->name('bantuan');

    Route::get('/pengumuman', function () {
        return Inertia::render('Peternak/Pengumuman');
    })->name('pengumuman');
});

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
