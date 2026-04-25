<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Webfox\Xero\OauthCredentialManager;


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::get('/xero/success', function () {
        return 'Xero connected successfully';
    })->name('xero.auth.success');

    Route::get('/xero/test', function () {
        $manager = app(\Webfox\Xero\OauthCredentialManager::class);

        dd($manager->getTenantId());
    });

    Route::post('/purchases/{purchase}/xero', [PurchaseController::class, 'pushToXero'])
    ->name('purchases.xero');


    Route::get('/contacts/search', [ContactController::class, 'search'])
    ->name('contacts.search');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::post('/products/bulk-delete', [ProductController::class, 'bulkDelete'])->name('products.bulk-delete');

    Route::resource('purchases', PurchaseController::class);
       Route::post('/purchases/bulk-delete', [PurchaseController::class, 'bulkDelete'])->name('purchases.bulk-delete');


    Route::get('/listings', [ListingController::class, 'index'])->name('listings.index');
    Route::get('/listings/create', [ListingController::class, 'create'])->name('listings.create');
    Route::post('/listings', [ListingController::class, 'store'])->name('listings.store');
    Route::post('/listings/bulk-delete', [ListingController::class, 'bulkDelete'])->name('listings.bulk-delete');
    Route::get('/listings/{listing}/edit', [ListingController::class, 'edit'])->name('listings.edit');


    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
