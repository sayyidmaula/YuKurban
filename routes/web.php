<?php

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
    Route::get('/beranda', function () {
        return Inertia::render('Penabung/Beranda');
    })->name('beranda');

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

    Route::get('/profil', function () {
        return Inertia::render('Peternak/Profil');
    })->name('profil');

    Route::get('/bantuan', function () {
        return Inertia::render('Peternak/Bantuan');
    })->name('bantuan');

    Route::get('/pengumuman', function () {
        return Inertia::render('Peternak/Pengumuman');
    })->name('pengumuman');
});

/*
|--------------------------------------------------------------------------
| Profile Routes (Breeze standard)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
