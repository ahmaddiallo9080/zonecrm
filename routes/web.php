<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientAccessController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InterventionController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoicePaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchasePaymentController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/recherche', SearchController::class)->name('search');

    // Clients & accès techniques
    Route::resource('clients', ClientController::class);
    Route::post('clients/{client}/accesses', [ClientAccessController::class, 'store'])->name('clients.accesses.store');
    Route::put('accesses/{access}', [ClientAccessController::class, 'update'])->name('accesses.update');
    Route::delete('accesses/{access}', [ClientAccessController::class, 'destroy'])->name('accesses.destroy');
    Route::post('accesses/{access}/reveal', [ClientAccessController::class, 'reveal'])
        ->middleware('throttle:30,1')->name('accesses.reveal');

    Route::resource('interventions', InterventionController::class);

    // Devis
    Route::resource('quotes', QuoteController::class);
    Route::get('quotes/{quote}/print', [QuoteController::class, 'print'])->name('quotes.print');
    Route::patch('quotes/{quote}/status', [QuoteController::class, 'status'])->name('quotes.status');
    Route::post('quotes/{quote}/convert', [QuoteController::class, 'convert'])->name('quotes.convert');
    Route::post('quotes/{quote}/duplicate', [QuoteController::class, 'duplicate'])->name('quotes.duplicate');

    // Factures & encaissements
    Route::resource('invoices', InvoiceController::class);
    Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::post('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
    Route::post('invoices/{invoice}/payments', [InvoicePaymentController::class, 'store'])->name('invoices.payments.store');
    Route::delete('invoice-payments/{payment}', [InvoicePaymentController::class, 'destroy'])->name('invoice-payments.destroy');

    // Stock & fournisseurs
    Route::resource('products', ProductController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('purchases', PurchaseController::class);
    Route::post('purchases/{purchase}/payments', [PurchasePaymentController::class, 'store'])->name('purchases.payments.store');
    Route::delete('purchase-payments/{payment}', [PurchasePaymentController::class, 'destroy'])->name('purchase-payments.destroy');

    // Finances
    Route::resource('expenses', ExpenseController::class)->except('show');
    Route::get('dettes', [DebtController::class, 'index'])->name('debts.index');
    Route::get('tresorerie', [ReportController::class, 'index'])->name('reports.index');

    Route::get('parametres', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('parametres', [SettingController::class, 'update'])->name('settings.update');
    Route::put('parametres/mot-de-passe', [SettingController::class, 'password'])->name('settings.password');
});
