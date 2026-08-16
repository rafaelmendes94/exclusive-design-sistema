<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\FactorTable;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Quote;
use App\Models\QuoteStatus;
use App\Models\SupplierSyncLog;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $quoteStatusCounts = QuoteStatus::query()
            ->withCount('quotes')
            ->orderBy('position')
            ->get();

        $quotesThisMonth = Quote::where('created_at', '>=', now()->startOfMonth())->count();
        $approvedStatusIds = QuoteStatus::whereIn('name', ['Aprovado', 'Aprovada'])->pluck('id');
        $approvedThisMonth = Quote::whereIn('quote_status_id', $approvedStatusIds)
            ->where('updated_at', '>=', now()->startOfMonth())
            ->count();

        return view('admin.dashboard', [
            'productsCount' => Product::count(),
            'variationsCount' => ProductVariation::count(),
            'quotesCount' => Quote::count(),
            'customersCount' => Customer::count(),
            'factorTablesCount' => FactorTable::count(),
            'activeProductsCount' => Product::where('status', 'active')->count(),
            'uncategorizedProductsCount' => Product::whereNull('category_id')->count(),
            'quotesThisMonth' => $quotesThisMonth,
            'approvedThisMonth' => $approvedThisMonth,
            'quoteStatusCounts' => $quoteStatusCounts,
            'latestProducts' => Product::withCount('variations')->latest()->limit(8)->get(),
            'latestQuotes' => Quote::with(['status', 'customer'])->latest()->limit(8)->get(),
            'latestSync' => SupplierSyncLog::latest()->first(),
        ]);
    }
}
