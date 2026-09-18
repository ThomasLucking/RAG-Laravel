<?php

use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', function () {
    return view('home');
});

Route::resource('documents', DocumentController::class)->except(['create', 'edit']);

Route::redirect('/formulaire', '/documents')->name('documents.formulaire');
