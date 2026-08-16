<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function home()
    {
        $settings = SiteSetting::mapped();
        $featuredProducts = Product::query()
            ->with('category')
            ->where('status', 'active')
            ->whereNotNull('image_url')
            ->where('image_url', '!=', '')
            ->latest()
            ->limit(10)
            ->get();
        $newProducts = Product::query()
            ->with('category')
            ->where('status', 'active')
            ->whereNotNull('image_url')
            ->where('image_url', '!=', '')
            ->latest('id')
            ->limit(8)
            ->get();
        $categories = Category::where('active', true)->orderBy('position')->orderBy('name')->limit(48)->get();
        $featuredCategories = Category::where('active', true)->where('featured', true)->orderBy('position')->limit(3)->get();

        return view('site.home', compact('settings', 'featuredProducts', 'newProducts', 'categories', 'featuredCategories'));
    }

    public function products(Request $request)
    {
        $settings = SiteSetting::mapped();
        $categories = Category::where('active', true)->orderBy('name')->get();
        $products = Product::query()
            ->with('category')
            ->where('status', 'active')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->string('q')->toString();
                $query->where(fn ($query) => $query->where('name', 'like', "%{$q}%")->orWhere('base_sku', 'like', "%{$q}%"));
            })
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->latest()
            ->paginate(24)
            ->withQueryString();

        return view('site.products', compact('settings', 'categories', 'products'));
    }

    public function product(Request $request)
    {
        $settings = SiteSetting::mapped();
        $product = Product::query()
            ->with(['category', 'variations.primaryColor', 'variations.secondaryColor'])
            ->where('status', 'active')
            ->where('base_sku', $request->string('codigo')->toString())
            ->firstOrFail();

        return view('site.product', compact('settings', 'product'));
    }
}
