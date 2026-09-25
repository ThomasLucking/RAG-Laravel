<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FullSearchTextController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/formulaire');

Route::resource('documents', DocumentController::class)->except(['create', 'edit']);

Route::redirect('/formulaire', '/documents')->name('documents.formulaire');

Route::get('/search', [FullSearchTextController::class, 'index'])->name('search.index');
Route::post('/search', [FullSearchTextController::class, 'userQuery'])->name('search.query');
