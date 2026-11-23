<?php

use App\Http\Controllers\CertificateEditController;
use App\Http\Controllers\CertificateExportController;
use App\Http\Controllers\CertificateNumberController;
use App\Http\Controllers\CertificateReview;
use App\Http\Controllers\RecipientController;
use App\Http\Controllers\TemplateUploadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [TemplateUploadController::class, 'index'])->name('home');
Route::post('/language', function (Request $request) {
    $validated = $request->validate([
        'locale' => ['required', 'in:id,en'],
    ]);

    $request->session()->put('locale', $validated['locale']);

    return back();
})->name('language.update');

Route::post('/templates', [TemplateUploadController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('templates.store');
Route::get('/templates/preview', [TemplateUploadController::class, 'preview'])->name('templates.preview');
Route::post('/templates/select/{id}', [TemplateUploadController::class, 'select'])
    ->name('templates.select');
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

Route::get('/certificate-export/pdf', [CertificateExportController::class, 'pdf'])
    ->middleware('throttle:5,1')
    ->name('certificate-export.pdf');

Route::get('/certificate-export/zip', [CertificateExportController::class, 'zip'])
    ->middleware('throttle:5,1')
    ->name('certificate-export.zip');
