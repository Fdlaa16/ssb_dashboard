<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Company\Http\Controllers\MediaController;
use Modules\Company\Http\Controllers\PlayerController;
use Modules\Company\Http\Controllers\ScheduleMatchController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/company', function (Request $request) {
//     return $request->user();
// });

Route::prefix('company')->group(function () {
    Route::get('player', [PlayerController::class, 'index'])->name('player.index');
    Route::get('player/create', [PlayerController::class, 'create'])->name('player.create');
    Route::post('player/store', [PlayerController::class, 'store'])->name('player.store');
    Route::get('player/{id}', [PlayerController::class, 'show'])->name('player.show');
    Route::get('player/{id}/edit', [PlayerController::class, 'edit'])->name('player.edit');
    Route::put('player/{id}', [PlayerController::class, 'update'])->name('player.update');
    Route::delete('player/{id}', [PlayerController::class, 'destroy'])->name('player.destroy');
    Route::put('player/{id}/active', [PlayerController::class, 'active'])->name('player.active');
    Route::put('player/{id}/approve', [PlayerController::class, 'approve'])->name('player.approve');
    Route::put('player/{id}/reject', [PlayerController::class, 'reject'])->name('player.reject');

    Route::get('media', [MediaController::class, 'index'])->name('media.index');
    Route::get('media/create', [MediaController::class, 'create'])->name('media.create');
    Route::post('media/store', [MediaController::class, 'store'])->name('media.store');
    Route::get('media/{id}', [MediaController::class, 'show'])->name('media.show');
    Route::get('media/{id}/edit', [MediaController::class, 'edit'])->name('media.edit');
    Route::put('media/{id}', [MediaController::class, 'update'])->name('media.update');
    Route::delete('media/{id}', [MediaController::class, 'destroy'])->name('media.destroy');
    Route::put('media/{id}/active', [MediaController::class, 'active'])->name('media.active');
    Route::put('media/{id}/approve', [MediaController::class, 'approve'])->name('media.approve');
    Route::put('media/{id}/reject', [MediaController::class, 'reject'])->name('media.reject');

    Route::get('schedule-match', [ScheduleMatchController::class, 'index'])->name('schedule-match.index');
    Route::get('schedule-match/create', [ScheduleMatchController::class, 'create'])->name('schedule-match.create');
    Route::post('schedule-match/store', [ScheduleMatchController::class, 'store'])->name('schedule-match.store');
    Route::get('schedule-match/{id}', [ScheduleMatchController::class, 'show'])->name('schedule-match.show');
    Route::get('schedule-match/{id}/edit', [ScheduleMatchController::class, 'edit'])->name('schedule-match.edit');
    Route::put('schedule-match/{id}', [ScheduleMatchController::class, 'update'])->name('schedule-match.update');
    Route::delete('schedule-match/{id}', [ScheduleMatchController::class, 'destroy'])->name('schedule-match.destroy');
    Route::put('schedule-match/{id}/active', [ScheduleMatchController::class, 'active'])->name('schedule-match.active');
    Route::put('schedule-match/{id}/approve', [ScheduleMatchController::class, 'approve'])->name('schedule-match.approve');
    Route::put('schedule-match/{id}/reject', [ScheduleMatchController::class, 'reject'])->name('schedule-match.reject');
});
