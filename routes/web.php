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
Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show')->where('document', '[a-z0-9-]+');
Route::put('/documents/{document}', [DocumentController::class, 'update'])->name('documents.update')->where('document', '[a-z0-9-]+');
