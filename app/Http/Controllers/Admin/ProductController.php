<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Color;
use App\Models\Engraving;
use App\Models\FactorTable;
use App\Models\Product;
use App\Models\Splash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->with(['category', 'factorTable'])
            ->withCount('variations')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->string('q')->toString();
                $query->where(function ($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")
                        ->orWhere('base_sku', 'like', "%{$q}%")
                        ->orWhere('supplier_product_id', 'like', "%{$q}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'factorTables' => FactorTable::where('active', true)->orderBy('title')->get(),
        ]);
    }

    public function edit(Product $product)
    {
        $product->load(['variations.primaryColor', 'variations.secondaryColor', 'manualPriceRanges', 'category', 'factorTable', 'splash', 'engravings']);

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'factorTables' => FactorTable::where('active', true)->orderBy('title')->get(),
            'splashes' => Splash::where('active', true)->orderBy('name')->get(),
            'engravings' => Engraving::where('active', true)->orderBy('name')->get(),
            'colors' => Color::where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'factor_table_id' => ['nullable', 'exists:factor_tables,id'],
            'splash_id' => ['nullable', 'exists:splashes,id'],
            'description' => ['nullable', 'string'],
            'availability' => ['nullable', 'string', 'max:120'],
            'additional_info' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'minimum_quantity' => ['nullable', 'integer', 'min:1'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'depth' => ['nullable', 'numeric', 'min:0'],
            'thickness' => ['nullable', 'numeric', 'min:0'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'circumference' => ['nullable', 'numeric', 'min:0'],
            'diameter' => ['nullable', 'numeric', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'energy' => ['nullable', 'string', 'max:120'],
            'warranty' => ['nullable', 'string', 'max:120'],
            'engraving_measure' => ['nullable', 'string', 'max:120'],
            'total_size' => ['nullable', 'string', 'max:120'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'technical_information' => ['nullable', 'string'],
            'engraving_description' => ['nullable', 'string'],
            'refill_description' => ['nullable', 'string', 'max:255'],
            'seo_keywords' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:255'],
            'seo_url' => ['nullable', 'string', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'engraving_ids' => ['nullable', 'array'],
            'engraving_ids.*' => ['exists:engravings,id'],
            'manual_prices' => ['nullable', 'array'],
            'block_supplier_update' => ['nullable', 'boolean'],
            'use_manual_price_table' => ['nullable', 'boolean'],
            'youtube_active' => ['nullable', 'boolean'],
        ]);

        $validated['block_supplier_update'] = $request->boolean('block_supplier_update');
        $validated['use_manual_price_table'] = $request->boolean('use_manual_price_table');
        $validated['youtube_active'] = $request->boolean('youtube_active');
        $validated['category_id'] = $validated['category_id'] ?: null;
        $validated['splash_id'] = $validated['splash_id'] ?: null;
        $engravingIds = $validated['engraving_ids'] ?? [];
        unset($validated['engraving_ids'], $validated['manual_prices']);
        $validated['factor_table_id'] = auth()->user()->can_view_factor
            ? (($validated['factor_table_id'] ?? null) ?: null)
            : $product->factor_table_id;
        if (!auth()->user()->can_view_cost) {
            unset($validated['cost_price'], $validated['sale_price']);
        }

        $product->update($validated);
        $product->engravings()->sync($engravingIds);
        $this->syncManualPrices($product, $request);

        if ($request->filled('category_name')) {
            $categoryName = $request->string('category_name')->toString();
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName, 'active' => true]
            );
            $product->update(['category_id' => $category->id]);
        }

        return back()->with('success', 'Produto atualizado.');
    }

    public function quickCost(Request $request, Product $product)
    {
        abort_unless(auth()->user()->can_view_cost, 403);

        $validated = $request->validate([
            'cost_price' => ['required', 'numeric', 'min:0'],
        ]);

        $product->update($validated);

        return back()->with('success', 'Custo atualizado.');
    }

    public function quickFactor(Request $request, Product $product)
    {
        abort_unless(auth()->user()->can_view_factor, 403);

        $validated = $request->validate([
            'factor_table_id' => ['nullable', 'exists:factor_tables,id'],
        ]);

        $product->update(['factor_table_id' => ($validated['factor_table_id'] ?? null) ?: null]);

        return back()->with('success', 'Tabela de fator atualizada.');
    }

    public function quickStatus(Request $request, Product $product)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive'],
        ]);

        $product->update($validated);

        return back()->with('success', 'Status atualizado.');
    }

    private function syncManualPrices(Product $product, Request $request): void
    {
        if (!auth()->user()->can_view_cost) {
            return;
        }

        $product->manualPriceRanges()->delete();

        foreach ($request->input('manual_prices', []) as $range) {
            if (!filled($range['quantity_from'] ?? null) || !filled($range['price'] ?? null)) {
                continue;
            }

            $product->manualPriceRanges()->create([
                'quantity_from' => (int) $range['quantity_from'],
                'quantity_to' => filled($range['quantity_to'] ?? null) ? (int) $range['quantity_to'] : null,
                'price' => (float) str_replace(',', '.', $range['price']),
            ]);
        }
    }
}
