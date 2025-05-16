<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\ClubController;
use Modules\Dashboard\Http\Controllers\PlayerController;
use Modules\Dashboard\Http\Controllers\ScheduleMatchController;
use Modules\Dashboard\Http\Controllers\ScheduleTrainingController;
use Modules\Dashboard\Http\Controllers\StadiumController;
use Modules\Dashboard\Http\Controllers\StandingController;

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

// Route::middleware('auth:api')->get('/dashboard', function (Request $request) {
//     return $request->user();
// });

Route::get('player', [PlayerController::class, 'index'])->name('player.index');
Route::get('player/create', [PlayerController::class, 'create'])->name('player.create');
Route::post('player/store', [PlayerController::class, 'store'])->name('player.store');
Route::get('player/{id}', [PlayerController::class, 'show'])->name('player.show');
Route::get('player/{id}/edit', [PlayerController::class, 'edit'])->name('player.edit');
Route::put('player/{id}', [PlayerController::class, 'update'])->name('player.update');
Route::delete('player/{id}', [PlayerController::class, 'destroy'])->name('player.destroy');

Route::get('club', [ClubController::class, 'index'])->name('club.index');
Route::get('club/create', [ClubController::class, 'create'])->name('club.create');
Route::post('club/store', [ClubController::class, 'store'])->name('club.store');
Route::get('club/{id}', [ClubController::class, 'show'])->name('club.show');
Route::get('club/{id}/edit', [ClubController::class, 'edit'])->name('club.edit');
Route::put('club/{id}', [ClubController::class, 'update'])->name('club.update');
Route::delete('club/{id}', [ClubController::class, 'destroy'])->name('club.destroy');

Route::get('stadium', [StadiumController::class, 'index'])->name('stadium.index');
Route::get('stadium/create', [StadiumController::class, 'create'])->name('stadium.create');
Route::post('stadium/store', [StadiumController::class, 'store'])->name('stadium.store');
Route::get('stadium/{id}', [StadiumController::class, 'show'])->name('stadium.show');
Route::get('stadium/{id}/edit', [StadiumController::class, 'edit'])->name('stadium.edit');
Route::put('stadium/{id}', [StadiumController::class, 'update'])->name('stadium.update');
Route::delete('stadium/{id}', [StadiumController::class, 'destroy'])->name('stadium.destroy');

Route::get('schedule-match', [ScheduleMatchController::class, 'index'])->name('schedule-match.index');
Route::get('schedule-match/create', [ScheduleMatchController::class, 'create'])->name('schedule-match.create');
Route::post('schedule-match/store', [ScheduleMatchController::class, 'store'])->name('schedule-match.store');
Route::get('schedule-match/{id}', [ScheduleMatchController::class, 'show'])->name('schedule-match.show');
Route::get('schedule-match/{id}/edit', [ScheduleMatchController::class, 'edit'])->name('schedule-match.edit');
Route::put('schedule-match/{id}', [ScheduleMatchController::class, 'update'])->name('schedule-match.update');
Route::delete('schedule-match/{id}', [ScheduleMatchController::class, 'destroy'])->name('schedule-match.destroy');

Route::get('schedule-training', [ScheduleTrainingController::class, 'index'])->name('schedule-training.index');
Route::get('schedule-training/create', [ScheduleTrainingController::class, 'create'])->name('schedule-training.create');
Route::post('schedule-training/store', [ScheduleTrainingController::class, 'store'])->name('schedule-training.store');
Route::get('schedule-training/{id}', [ScheduleTrainingController::class, 'show'])->name('schedule-training.show');
Route::get('schedule-training/{id}/edit', [ScheduleTrainingController::class, 'edit'])->name('schedule-training.edit');
Route::put('schedule-training/{id}', [ScheduleTrainingController::class, 'update'])->name('schedule-training.update');
Route::delete('schedule-training/{id}', [ScheduleTrainingController::class, 'destroy'])->name('schedule-training.destroy');

Route::get('standing-match', [StandingController::class, 'indexStandingMatch'])->name('standing.indexStandingMatch');
Route::get('standing-training', [StandingController::class, 'indexStandingTraining'])->name('standing.indexStandingTraining');
Route::get('standing/create', [StandingController::class, 'create'])->name('standing.create');
Route::post('standing/store', [StandingController::class, 'store'])->name('standing.store');
Route::get('standing/{id}', [StandingController::class, 'show'])->name('standing.show');
Route::get('standing/{id}/edit', [StandingController::class, 'edit'])->name('standing.edit');
Route::put('standing/{id}', [StandingController::class, 'update'])->name('standing.update');
Route::delete('standing/{id}', [StandingController::class, 'destroy'])->name('standing.destroy');
