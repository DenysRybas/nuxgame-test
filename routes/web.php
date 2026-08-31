<?php

use App\Http\Controllers\LinkController;
use App\Http\Controllers\LuckController;
use App\Http\Controllers\RegistrationController;
use App\Http\Middleware\EnsureLinkIsValid;
use Illuminate\Support\Facades\Route;

Route::get('/', [RegistrationController::class, 'create'])->name('home');
Route::post('/', [RegistrationController::class, 'store'])->name('register');

Route::middleware(EnsureLinkIsValid::class)->group(function (): void {
    Route::get('/luck/{link}', [LuckController::class, 'show'])->name('luck');
    Route::post('/luck/{link}/generate', [LuckController::class, 'generate'])->name('luck.generate');
    Route::get('/luck/{link}/history', [LuckController::class, 'history'])->name('luck.history');
    Route::post('/luck/{link}/regenerate', [LinkController::class, 'regenerate'])->name('link.regenerate');
    Route::post('/luck/{link}/deactivate', [LinkController::class, 'deactivate'])->name('link.deactivate');
});
