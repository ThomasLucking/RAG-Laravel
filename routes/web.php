<?php

use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', function () {
    return view('home');
});

Route::get('/formulaire', [DocumentController::class, 'create'])->name('documents.create');
Route::post('/formulaire', [DocumentController::class, 'store'])->name('documents.store');
