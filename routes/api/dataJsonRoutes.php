<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataJson\JsonController;

Route::prefix('data')->group(function () {
    Route::get('manipulate-json', [JsonController::class, 'manipulateJson'])->name('manipulate-json');
});

