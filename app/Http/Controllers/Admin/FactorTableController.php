<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FactorTable;
use App\Models\Product;
use Illuminate\Http\Request;

class FactorTableController extends Controller
{
    public function index()
    {
        return view('admin.factors.index', [
            'factorTables' => FactorTable::with(['ranges' => fn ($query) => $query->orderBy('quantity_from')])
                ->withCount('ranges')
                ->orderBy('title')
                ->get(),
        ]);
    }

    public function create()
    {
        return view('admin.factors.edit', ['factorTable' => new FactorTable(['active' => true])]);
    }

    public function store(Request $request)
    {
        $factorTable = FactorTable::create($this->validatedTable($request));
        $this->syncRanges($factorTable, $request);

        return redirect()->route('admin.factors.edit', $factorTable)->with('success', 'Tabela criada.');
    }

    public function edit(FactorTable $factor)
    {
        $factor->load('ranges');

        return view('admin.factors.edit', ['factorTable' => $factor]);
    }

    public function update(Request $request, FactorTable $factor)
    {
        $factor->update($this->validatedTable($request));
        $this->syncRanges($factor, $request);

        if ($request->input('action') === 'update_products') {
            Product::where('factor_table_id', $factor->id)->update(['use_manual_price_table' => false]);

            return back()->with('success', 'Tabela atualizada e produtos vinculados preparados para usar o fator.');
        }

        return back()->with('success', 'Tabela atualizada.');
    }

    public function duplicate(FactorTable $factor)
    {
        $copy = $factor->replicate(['title']);
        $copy->title = $factor->title.' - cópia';
        $copy->save();

        foreach ($factor->ranges as $range) {
            $copy->ranges()->create($range->only(['quantity_from', 'quantity_to', 'coefficient']));
        }

        return redirect()->route('admin.factors.edit', $copy)->with('success', 'Tabela duplicada.');
    }

    private function validatedTable(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'active' => ['nullable', 'boolean'],
        ]);

        $validated['active'] = $request->boolean('active');

        return $validated;
    }

    private function syncRanges(FactorTable $factorTable, Request $request): void
    {
        $factorTable->ranges()->delete();

        foreach ($request->input('ranges', []) as $range) {
            $factorPercent = $range['product_factor_percent'] ?? $range['coefficient'] ?? null;

            if (!filled($range['quantity_from'] ?? null) || !filled($factorPercent)) {
                continue;
            }

            $factorPercent = (float) str_replace(',', '.', $factorPercent);

            $factorTable->ranges()->create([
                'quantity_from' => (int) $range['quantity_from'],
                'quantity_to' => filled($range['quantity_to'] ?? null) ? (int) $range['quantity_to'] : null,
                'coefficient' => $factorPercent > 0 ? 100 / $factorPercent : 1,
            ]);
        }
    }
}
