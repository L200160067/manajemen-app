<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
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
    Volt::route('clients', 'clients.client-index')->name('clients.index');
    Volt::route('clients/create', 'clients.client-form')->name('clients.create');
    Volt::route('clients/{client}/edit', 'clients.client-form')->name('clients.edit');

    Volt::route('products', 'products.product-index')->name('products.index');
    Volt::route('products/create', 'products.product-form')->name('products.create');
    Volt::route('products/{product}/edit', 'products.product-form')->name('products.edit');

    Volt::route('invoices', 'invoices.invoice-index')->name('invoices.index');
    Volt::route('invoices/create', 'invoices.invoice-form')->name('invoices.create');
    Volt::route('invoices/{invoice}/edit', 'invoices.invoice-form')->name('invoices.edit');
    Volt::route('invoices/{invoice}', 'invoices.invoice-show')->name('invoices.show'); 
    
    // items route might not be needed for UI anymore, but keeping for now or removing?
    // Route::resource('invoices.items', InvoiceItemController::class)->only(['store', 'update', 'destroy'])->shallow();
});

require __DIR__.'/settings.php';
