<?php

use App\Http\Controllers\Admin\BoardController as AdminBoardController;
use App\Http\Controllers\Admin\CardController as AdminCardController;
use App\Http\Controllers\Public\BoardController as PublicBoardController;
use App\Http\Controllers\Public\CardClaimController as PublicCardClaimController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', fn () => Inertia::render('Dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/boards', [AdminBoardController::class, 'index'])->name('boards.index');
    Route::post('/boards', [AdminBoardController::class, 'store'])->name('boards.store');
    Route::get('/boards/{board}', [AdminBoardController::class, 'show'])->name('boards.show');
    Route::patch('/boards/{board}', [AdminBoardController::class, 'update'])->name('boards.update');
    Route::delete('/boards/{board}', [AdminBoardController::class, 'destroy'])->name('boards.destroy');
    Route::post('/boards/{board}/reset', [AdminBoardController::class, 'reset'])->name('boards.reset');
    Route::get('/boards/{board}/export', [AdminBoardController::class, 'exportCsv'])->name('boards.export');
    Route::patch('/cards/{card}', [AdminCardController::class, 'update'])->name('cards.update');
});

Route::get('/boards', [PublicBoardController::class, 'index'])->name('boards.index');
Route::get('/boards/{board}', [PublicBoardController::class, 'show'])->name('boards.show');
Route::post('/boards/{board}/cards/{cardUuid}/claim', [PublicCardClaimController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('boards.cards.claim');

require __DIR__.'/settings.php';
