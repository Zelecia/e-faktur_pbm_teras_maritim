<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PdfController;

Route::get('/faktur/{id}/pdf', [PdfController::class, 'pdf'])->name('faktur.pdf');
