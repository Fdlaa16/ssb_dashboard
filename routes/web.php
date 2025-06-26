<?php

use Illuminate\Support\Facades\Route;

// Hanya tangkap route web, bukan route API
Route::get('{any}', function () {
    return view('application');
})->where('any', '^(?!api|storage).*$');
