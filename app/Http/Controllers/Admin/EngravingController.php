<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Engraving;
use Illuminate\Http\Request;

class EngravingController extends Controller
{
    public function index(Request $request)
    {
        $engravings = Engraving::query()
            ->withCount('priceRanges')
            ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%'))
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('admin.engravings.index', compact('engravings'));
    }

    public function create()
    {
        return view('admin.engravings.edit', ['engraving' => new Engraving(['active' => true])]);
    }

    public function store(Request $request)
    {
        $engraving = Engraving::create($this->validated($request));
        $this->syncRanges($engraving, $request);

        return redirect()->route('admin.engravings.edit', $engraving)->with('success', 'Gravação criada.');
    }

    public function edit(Engraving $engraving)
    {
        $engraving->load('priceRanges');

        return view('admin.engravings.edit', compact('engraving'));
    }

    public function update(Request $request, Engraving $engraving)
    {
        $engraving->update($this->validated($request));
        $this->syncRanges($engraving, $request);

        return back()->with('success', 'Gravação atualizada.');
    }

    public function duplicate(Engraving $engraving)
    {
        $copy = $engraving->replicate(['name']);
        $copy->name = $engraving->name.' - cópia';
        $copy->save();

        foreach ($engraving->priceRanges as $range) {
            $copy->priceRanges()->create($range->only(['quantity_from', 'quantity_to', 'price']));
        }

        return redirect()->route('admin.engravings.edit', $copy)->with('success', 'Gravação clonada.');
    }

    public function destroy(Engraving $engraving)
    {
        $engraving->delete();

        return redirect()->route('admin.engravings.index')->with('success', 'Gravação excluída.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
        ]);
        $validated['active'] = $request->boolean('active');

        return $validated;
    }

    private function syncRanges(Engraving $engraving, Request $request): void
    {
        $engraving->priceRanges()->delete();

        foreach ($request->input('ranges', []) as $range) {
            if (!filled($range['quantity_from'] ?? null) || !filled($range['price'] ?? null)) {
                continue;
            }

            $engraving->priceRanges()->create([
                'quantity_from' => (int) $range['quantity_from'],
                'quantity_to' => filled($range['quantity_to'] ?? null) ? (int) $range['quantity_to'] : null,
                'price' => (float) str_replace(',', '.', $range['price']),
            ]);
        }
    }
}
