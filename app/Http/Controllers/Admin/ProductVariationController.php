<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;

class ProductVariationController extends Controller
{
    public function update(Request $request, Product $product, ProductVariation $variation)
    {
        abort_unless($variation->product_id === $product->id, 404);

        $validated = $request->validate([
            'color_id' => ['nullable', 'exists:colors,id'],
            'secondary_color_id' => ['nullable', 'exists:colors,id'],
            'status' => ['required', 'in:active,inactive'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'main_stock' => ['nullable', 'integer', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'image_url' => ['nullable', 'url', 'max:255'],
        ]);

        if (!auth()->user()->can_view_cost) {
            unset($validated['sale_price'], $validated['cost_price']);
        }

        $validated['color_id'] = $validated['color_id'] ?: null;
        $validated['secondary_color_id'] = $validated['secondary_color_id'] ?: null;
        $variation->update($validated);

        return back()->with('success', 'Variação atualizada.');
    }
}
