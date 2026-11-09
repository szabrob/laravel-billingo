<?php

use Illuminate\Support\Facades\Route;
use Workbench\App\Http\Controllers\PlaygroundController;

Route::get('/', [PlaygroundController::class, 'index'])->name('playground');
Route::post('/playground/call', [PlaygroundController::class, 'call'])->name('playground.call');
