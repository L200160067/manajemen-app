<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InvoiceItemController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('clients', ClientController::class);
    Route::resource('products', ProductController::class);
    Route::resource('invoices', InvoiceController::class);
    Route::resource('invoices.items', InvoiceItemController::class)->only(['store', 'update', 'destroy'])->shallow();
});

require __DIR__.'/settings.php';
