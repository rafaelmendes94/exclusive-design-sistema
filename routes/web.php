<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\BusinessSegmentController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\ColorGroupController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EngravingController;
use App\Http\Controllers\Admin\FactorTableController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductVariationController;
use App\Http\Controllers\Admin\QuoteController;
use App\Http\Controllers\Admin\QuoteStatusController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\SplashController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\SiteController;

Route::get('/', [SiteController::class, 'home'])->name('site.home');
Route::get('produtos', [SiteController::class, 'products'])->name('site.products');
Route::get('produto', [SiteController::class, 'product'])->name('site.product');
Route::get('proposta/{token}', [QuoteController::class, 'publicProposal'])->name('quotes.public');

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');
});
Route::post('logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('cadastros/{type}', [UserController::class, 'index'])->name('users.index');
    Route::get('cadastros/{type}/novo', [UserController::class, 'create'])->name('users.create');
    Route::post('cadastros/{type}', [UserController::class, 'store'])->name('users.store');
    Route::get('cadastros/{type}/{user}/editar', [UserController::class, 'edit'])->name('users.edit');
    Route::put('cadastros/{type}/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('cadastros/{type}/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::resource('clientes', CustomerController::class)
        ->parameters(['clientes' => 'customer'])
        ->names('customers')
        ->except('show');
    Route::get('ramos', [BusinessSegmentController::class, 'index'])->name('segments.index');
    Route::post('ramos', [BusinessSegmentController::class, 'store'])->name('segments.store');
    Route::put('ramos/{segment}', [BusinessSegmentController::class, 'update'])->name('segments.update');
    Route::delete('ramos/{segment}', [BusinessSegmentController::class, 'destroy'])->name('segments.destroy');

    Route::get('status-orcamento', [QuoteStatusController::class, 'index'])->name('quote-statuses.index');
    Route::post('status-orcamento', [QuoteStatusController::class, 'store'])->name('quote-statuses.store');
    Route::put('status-orcamento/{status}', [QuoteStatusController::class, 'update'])->name('quote-statuses.update');
    Route::delete('status-orcamento/{status}', [QuoteStatusController::class, 'destroy'])->name('quote-statuses.destroy');

    Route::get('produtos', [ProductController::class, 'index'])->name('products.index');
    Route::get('produtos/{product}/editar', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('produtos/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::patch('produtos/{product}/custo', [ProductController::class, 'quickCost'])->name('products.quick-cost');
    Route::patch('produtos/{product}/fator', [ProductController::class, 'quickFactor'])->name('products.quick-factor');
    Route::patch('produtos/{product}/status', [ProductController::class, 'quickStatus'])->name('products.quick-status');
    Route::put('produtos/{product}/variacoes/{variation}', [ProductVariationController::class, 'update'])->name('products.variations.update');

    Route::resource('categorias', CategoryController::class)->parameters(['categorias' => 'category'])->names('categories')->except('show');
    Route::get('categorias-selecionadas', [CategoryController::class, 'featured'])->name('categories.featured');
    Route::put('categorias-selecionadas', [CategoryController::class, 'updateFeatured'])->name('categories.featured.update');
    Route::get('grupos-cores', [ColorGroupController::class, 'index'])->name('color-groups.index');
    Route::post('grupos-cores', [ColorGroupController::class, 'store'])->name('color-groups.store');
    Route::put('grupos-cores/{group}', [ColorGroupController::class, 'update'])->name('color-groups.update');
    Route::delete('grupos-cores/{group}', [ColorGroupController::class, 'destroy'])->name('color-groups.destroy');
    Route::get('cores', [ColorController::class, 'index'])->name('colors.index');
    Route::post('cores', [ColorController::class, 'store'])->name('colors.store');
    Route::put('cores/{color}', [ColorController::class, 'update'])->name('colors.update');
    Route::delete('cores/{color}', [ColorController::class, 'destroy'])->name('colors.destroy');
    Route::resource('gravacoes', EngravingController::class)->parameters(['gravacoes' => 'engraving'])->names('engravings')->except('show');
    Route::post('gravacoes/{engraving}/duplicar', [EngravingController::class, 'duplicate'])->name('engravings.duplicate');
    Route::get('splashes', [SplashController::class, 'index'])->name('splashes.index');
    Route::post('splashes', [SplashController::class, 'store'])->name('splashes.store');
    Route::put('splashes/{splash}', [SplashController::class, 'update'])->name('splashes.update');
    Route::delete('splashes/{splash}', [SplashController::class, 'destroy'])->name('splashes.destroy');

    Route::get('fatores', [FactorTableController::class, 'index'])->name('factors.index');
    Route::get('fatores/novo', [FactorTableController::class, 'create'])->name('factors.create');
    Route::post('fatores', [FactorTableController::class, 'store'])->name('factors.store');
    Route::get('fatores/{factor}/editar', [FactorTableController::class, 'edit'])->name('factors.edit');
    Route::put('fatores/{factor}', [FactorTableController::class, 'update'])->name('factors.update');
    Route::post('fatores/{factor}/duplicar', [FactorTableController::class, 'duplicate'])->name('factors.duplicate');
    Route::get('cms-site', [SiteSettingController::class, 'edit'])->name('site-settings.edit');
    Route::put('cms-site', [SiteSettingController::class, 'update'])->name('site-settings.update');
    Route::resource('fornecedores', SupplierController::class)
        ->parameters(['fornecedores' => 'supplier'])
        ->names('suppliers')
        ->except('show');

    Route::get('orcamentos', [QuoteController::class, 'index'])->name('quotes.index');
    Route::post('orcamentos', [QuoteController::class, 'create'])->name('quotes.create');
    Route::get('orcamentos/clientes/busca', [QuoteController::class, 'searchCustomers'])->name('quotes.customers.search');
    Route::get('orcamentos/produtos/busca', [QuoteController::class, 'searchProducts'])->name('quotes.products.search');
    Route::get('orcamentos/{quote}/editar', [QuoteController::class, 'edit'])->name('quotes.edit');
    Route::get('orcamentos/{quote}/proposta', [QuoteController::class, 'proposal'])->name('quotes.proposal');
    Route::put('orcamentos/{quote}', [QuoteController::class, 'update'])->name('quotes.update');
    Route::patch('orcamentos/{quote}/status', [QuoteController::class, 'quickStatus'])->name('quotes.quick-status');
    Route::post('orcamentos/{quote}/link-publico', [QuoteController::class, 'share'])->name('quotes.share');
    Route::post('orcamentos/{quote}/duplicar', [QuoteController::class, 'duplicate'])->name('quotes.duplicate');
    Route::post('orcamentos/{quote}/cliente', [QuoteController::class, 'storeCustomer'])->name('quotes.customers.store');
    Route::post('orcamentos/{quote}/itens', [QuoteController::class, 'addItem'])->name('quotes.items.add');
    Route::put('orcamentos/{quote}/itens/{item}', [QuoteController::class, 'updateItem'])->name('quotes.items.update');
    Route::delete('orcamentos/{quote}/itens/{item}', [QuoteController::class, 'destroyItem'])->name('quotes.items.destroy');
});
