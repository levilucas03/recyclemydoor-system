<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FuelLogController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Webfox\Xero\OauthCredentialManager;


// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });



Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('fuel-logs', FuelLogController::class);
    Route::post('/fuel-logs/bulk-delete', [FuelLogController::class, 'bulkDelete'])->name('fuel-logs.bulk-delete');


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

    Route::get('/products/search', function (Illuminate\Http\Request $request) {
        return \App\Models\Product::query()
            ->with('prices', 'primaryImage')
            ->where('title', 'like', "%{$request->q}%")
            ->orWhere('sku', 'like', "%{$request->q}%")
            ->limit(10)
            ->get()
            ->map(function ($product) {

                // dd($product->primaryImage->path);
                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'image' => '/storage/' . $product->primaryImage->path ?? '',
                    'size' => "{$product->width} x {$product->height}",
                    'price' => $product->prices->firstWhere('type', 'website')->price ?? 0
                ];
            });
    })->name('products.search');

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

    Route::resource('sales', SaleController::class);
    Route::post('/sales/bulk-delete', [SaleController::class, 'bulkDelete']);
});

require __DIR__.'/auth.php';
