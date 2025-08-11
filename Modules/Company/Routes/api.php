<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Company\Http\Controllers\AuthController;
use Modules\Company\Http\Controllers\ClubController;
use Modules\Company\Http\Controllers\MediaController;
use Modules\Company\Http\Controllers\PlayerController;
use Modules\Company\Http\Controllers\ScheduleMatchController;
use Modules\Company\Http\Controllers\ScheduleTrainingController;
use Modules\Company\Http\Controllers\SlideHomeController;
use Modules\Company\Http\Controllers\StandingController;
use Modules\Company\Http\Controllers\StructureController;

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
    Route::post('/login', [AuthController::class, 'apiLogin']);
    Route::post('/register', [AuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('profile', [AuthController::class, 'profile'])->name('profile');
        Route::put('profile-update', [AuthController::class, 'profileUpdate'])->name('profile-update');
    });

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
    Route::put('player/password-update', [PlayerController::class, 'passwordUpdate'])->name('player.password-update');

    Route::get('structure', [StructureController::class, 'index'])->name('structure.index');
    Route::get('structure/create', [StructureController::class, 'create'])->name('structure.create');
    Route::post('structure/store', [StructureController::class, 'store'])->name('structure.store');
    Route::get('structure/{id}', [StructureController::class, 'show'])->name('structure.show');
    Route::get('structure/{id}/edit', [StructureController::class, 'edit'])->name('structure.edit');
    Route::put('structure/{id}', [StructureController::class, 'update'])->name('structure.update');
    Route::delete('structure/{id}', [StructureController::class, 'destroy'])->name('structure.destroy');
    Route::put('structure/{id}/active', [StructureController::class, 'active'])->name('structure.active');
    Route::put('structure/{id}/approve', [StructureController::class, 'approve'])->name('structure.approve');
    Route::put('structure/{id}/reject', [StructureController::class, 'reject'])->name('structure.reject');
    Route::put('structure/password-update', [StructureController::class, 'passwordUpdate'])->name('structure.password-update');

    Route::get('club', [ClubController::class, 'index'])->name('club.index');
    Route::get('club/create', [ClubController::class, 'create'])->name('club.create');
    Route::post('club/store', [ClubController::class, 'store'])->name('club.store');
    Route::get('club/{id}', [ClubController::class, 'show'])->name('club.show');
    Route::get('club/{id}/edit', [ClubController::class, 'edit'])->name('club.edit');
    Route::put('club/{id}', [ClubController::class, 'update'])->name('club.update');
    Route::delete('club/{id}', [ClubController::class, 'destroy'])->name('club.destroy');
    Route::put('club/{id}/active', [ClubController::class, 'active'])->name('club.active');
    Route::put('club/{id}/approve', [ClubController::class, 'approve'])->name('club.approve');
    Route::put('club/{id}/reject', [ClubController::class, 'reject'])->name('club.reject');

    Route::get('nearest-media', [MediaController::class, 'nearestMedia'])->name('nearest-media.index');
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

    Route::get('nearest-matches', [ScheduleMatchController::class, 'nearestMatches'])->name('nearest-matches.index');
    Route::get('list-matches', [ScheduleMatchController::class, 'listMatches'])->name('list-matches.index');

    Route::get('nearest-trainings', [ScheduleTrainingController::class, 'nearestTrainings'])->name('nearest-trainings.index');
    Route::get('list-trainings', [ScheduleTrainingController::class, 'listTrainings'])->name('list-trainings.index');

    Route::get('slide_home', [SlideHomeController::class, 'index'])->name('slide_home.index');
    Route::get('slide_home/create', [SlideHomeController::class, 'create'])->name('slide_home.create');
    Route::post('slide_home/store', [SlideHomeController::class, 'store'])->name('slide_home.store');
    Route::get('slide_home/{id}', [SlideHomeController::class, 'show'])->name('slide_home.show');
    Route::get('slide_home/{id}/edit', [SlideHomeController::class, 'edit'])->name('slide_home.edit');
    Route::put('slide_home/{id}', [SlideHomeController::class, 'update'])->name('slide_home.update');
    Route::delete('slide_home/{id}', [SlideHomeController::class, 'destroy'])->name('slide_home.destroy');
    Route::put('slide_home/{id}/active', [SlideHomeController::class, 'active'])->name('slide_home.active');

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

    Route::get('standing', [StandingController::class, 'index'])->name('standing.index');
    Route::get('standing/create', [StandingController::class, 'create'])->name('standing.create');
    Route::post('standing/store', [StandingController::class, 'store'])->name('standing.store');
    Route::get('standing/{id}', [StandingController::class, 'show'])->name('standing.show');
    Route::get('standing/{id}/edit', [StandingController::class, 'edit'])->name('standing.edit');
    Route::put('standing/{id}', [StandingController::class, 'update'])->name('standing.update');
    Route::delete('standing/{id}', [StandingController::class, 'destroy'])->name('standing.destroy');
    Route::put('standing/{id}/active', [StandingController::class, 'active'])->name('standing.active');
    Route::put('standing/{id}/approve', [StandingController::class, 'approve'])->name('standing.approve');
    Route::put('standing/{id}/reject', [StandingController::class, 'reject'])->name('standing.reject');
});
