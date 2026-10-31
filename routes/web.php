<?php

use App\Http\Controllers\RecipientController;
use App\Http\Controllers\TemplateUploadController;
use App\Http\Controllers\CertificateEditController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CertificateNumberController;
use App\Http\Controllers\CertificateReview;

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

Route::get('/certificate-number', [CertificateNumberController::class, 'index'])->name('certificate-number.index');
Route::post('/certificate-number/continue', [CertificateNumberController::class, 'store'])
    ->name('certificate-number.store');

Route::get('/certificate-edit', [CertificateEditController::class, 'index'])
    ->name('certificate-edit.index');

Route::post('/certificate-edit/save', [CertificateEditController::class, 'save'])
    ->name('certificate-edit.save');

Route::get('/certificate-review', [CertificateReview::class, 'index'])
    ->name('certificate-review.index');