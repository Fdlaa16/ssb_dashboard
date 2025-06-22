<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\ClubController;
use Modules\Dashboard\Http\Controllers\MediaController;
use Modules\Dashboard\Http\Controllers\PlayerController;
use Modules\Dashboard\Http\Controllers\ScheduleMatchController;
use Modules\Dashboard\Http\Controllers\ScheduleTrainingController;
use Modules\Dashboard\Http\Controllers\SportController;
use Modules\Dashboard\Http\Controllers\StadiumController;
use Modules\Dashboard\Http\Controllers\StandingController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('hehe', [PlayerController::class, 'hehe'])->name('hehe');
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

Route::get('club', [ClubController::class, 'index'])->name('club.index');
Route::get('club/create', [ClubController::class, 'create'])->name('club.create');
Route::post('club/store', [ClubController::class, 'store'])->name('club.store');
Route::get('club/{id}', [ClubController::class, 'show'])->name('club.show');
Route::get('club/{id}/edit', [ClubController::class, 'edit'])->name('club.edit');
Route::put('club/{id}', [ClubController::class, 'update'])->name('club.update');
Route::delete('club/{id}', [ClubController::class, 'destroy'])->name('club.destroy');
Route::put('club/{id}/active', [ClubController::class, 'active'])->name('club.active');

Route::get('media', [MediaController::class, 'index'])->name('media.index');
Route::get('media/create', [MediaController::class, 'create'])->name('media.create');
Route::post('media/store', [MediaController::class, 'store'])->name('media.store');
Route::get('media/{id}', [MediaController::class, 'show'])->name('media.show');
Route::get('media/{id}/edit', [MediaController::class, 'edit'])->name('media.edit');
Route::put('media/{id}', [MediaController::class, 'update'])->name('media.update');
Route::delete('media/{id}', [MediaController::class, 'destroy'])->name('media.destroy');
Route::put('media/{id}/active', [MediaController::class, 'active'])->name('media.active');

Route::get('sport', [SportController::class, 'index'])->name('sport.index');
Route::get('sport/create', [SportController::class, 'create'])->name('sport.create');
Route::post('sport/store', [SportController::class, 'store'])->name('sport.store');
Route::get('sport/{id}', [SportController::class, 'show'])->name('sport.show');
Route::get('sport/{id}/edit', [SportController::class, 'edit'])->name('sport.edit');
Route::put('sport/{id}', [SportController::class, 'update'])->name('sport.update');
Route::delete('sport/{id}', [SportController::class, 'destroy'])->name('sport.destroy');

Route::get('stadium', [StadiumController::class, 'index'])->name('stadium.index');
Route::get('stadium/create', [StadiumController::class, 'create'])->name('stadium.create');
Route::post('stadium/store', [StadiumController::class, 'store'])->name('stadium.store');
Route::get('stadium/{id}', [StadiumController::class, 'show'])->name('stadium.show');
Route::get('stadium/{id}/edit', [StadiumController::class, 'edit'])->name('stadium.edit');
Route::put('stadium/{id}', [StadiumController::class, 'update'])->name('stadium.update');
Route::delete('stadium/{id}', [StadiumController::class, 'destroy'])->name('stadium.destroy');
Route::put('stadium/{id}/active', [StadiumController::class, 'active'])->name('stadium.active');

Route::get('schedule-match', [ScheduleMatchController::class, 'index'])->name('schedule-match.index');
Route::get('schedule-match/create', [ScheduleMatchController::class, 'create'])->name('schedule-match.create');
Route::post('schedule-match/store', [ScheduleMatchController::class, 'store'])->name('schedule-match.store');
Route::get('schedule-match/{id}', [ScheduleMatchController::class, 'show'])->name('schedule-match.show');
Route::get('schedule-match/{id}/edit', [ScheduleMatchController::class, 'edit'])->name('schedule-match.edit');
Route::put('schedule-match/{id}', [ScheduleMatchController::class, 'update'])->name('schedule-match.update');
Route::delete('schedule-match/{id}', [ScheduleMatchController::class, 'destroy'])->name('schedule-match.destroy');
Route::put('schedule-match/{id}/active', [ScheduleMatchController::class, 'active'])->name('schedule-match.active');

Route::get('schedule-training', [ScheduleTrainingController::class, 'index'])->name('schedule-training.index');
Route::get('schedule-training/create', [ScheduleTrainingController::class, 'create'])->name('schedule-training.create');
Route::post('schedule-training/store', [ScheduleTrainingController::class, 'store'])->name('schedule-training.store');
Route::get('schedule-training/{id}', [ScheduleTrainingController::class, 'show'])->name('schedule-training.show');
Route::get('schedule-training/{id}/edit', [ScheduleTrainingController::class, 'edit'])->name('schedule-training.edit');
Route::put('schedule-training/{id}', [ScheduleTrainingController::class, 'update'])->name('schedule-training.update');
Route::delete('schedule-training/{id}', [ScheduleTrainingController::class, 'destroy'])->name('schedule-training.destroy');
Route::put('schedule-training/{id}/active', [ScheduleTrainingController::class, 'active'])->name('schedule-training.active');

Route::get('standing-match', [StandingController::class, 'indexStandingMatch'])->name('standing.indexStandingMatch');
Route::get('standing-training', [StandingController::class, 'indexStandingTraining'])->name('standing.indexStandingTraining');
Route::get('standing/create', [StandingController::class, 'create'])->name('standing.create');
Route::post('standing/store', [StandingController::class, 'store'])->name('standing.store');
Route::get('standing/{id}', [StandingController::class, 'show'])->name('standing.show');
Route::get('standing/{id}/edit', [StandingController::class, 'edit'])->name('standing.edit');
Route::put('standing/{id}', [StandingController::class, 'update'])->name('standing.update');
Route::delete('standing/{id}', [StandingController::class, 'destroy'])->name('standing.destroy');
