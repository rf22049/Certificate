<?php

use App\Http\Controllers\TemplateUploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TemplateUploadController::class, 'index'])->name('home');
Route::post('/templates', [TemplateUploadController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('templates.store');
Route::get('/templates/preview', [TemplateUploadController::class, 'preview'])->name('templates.preview');
Route::delete('/templates', [TemplateUploadController::class, 'destroy'])->name('templates.destroy');
