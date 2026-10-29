<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/sertifikat/penomoran', function () {
    return view('certificate.step3');
})->name('certificate.step3');
