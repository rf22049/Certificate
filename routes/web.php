<?php

use App\Http\Controllers\RecipientController;
use App\Http\Controllers\TemplateUploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TemplateUploadController::class, 'index'])->name('home');
Route::post('/templates', [TemplateUploadController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('templates.store');
Route::get('/templates/preview', [TemplateUploadController::class, 'preview'])->name('templates.preview');
Route::delete('/templates', [TemplateUploadController::class, 'destroy'])->name('templates.destroy');

Route::get('/recipients', [RecipientController::class, 'index'])->name('recipients.index');
Route::post('/recipients/import', [RecipientController::class, 'import'])
    ->middleware('throttle:10,1')
    ->name('recipients.import');
Route::post('/recipients/manual', [RecipientController::class, 'store'])->name('recipients.store');
Route::post('/recipients/continue', [RecipientController::class, 'next'])->name('recipients.next');
