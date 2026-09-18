<?php

use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/formulaire');

Route::resource('documents', DocumentController::class)->except(['create', 'edit']);

Route::redirect('/formulaire', '/documents')->name('documents.formulaire');
