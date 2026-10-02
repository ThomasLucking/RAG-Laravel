<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FullSearchTextController;
use App\Http\Controllers\MeaningSearchController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/formulaire');

Route::resource('documents', DocumentController::class)->except(['create', 'edit']);

Route::redirect('/formulaire', '/documents')->name('documents.formulaire');

Route::get('/search', [FullSearchTextController::class, 'index'])->name('search.index');
Route::post('/query', MeaningSearchController::class)->name('user.query')->middleware('throttle:meaning-search');
