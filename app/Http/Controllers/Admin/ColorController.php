<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Models\ColorGroup;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    public function index(Request $request)
    {
        $colors = Color::query()
            ->with('group')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->string('q')->toString();
                $query->where(fn ($query) => $query->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%"));
            })
            ->when($request->filled('group'), fn ($query) => $query->where('color_group_id', $request->integer('group')))
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('admin.colors.index', [
            'colors' => $colors,
            'groups' => ColorGroup::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Color::create($this->validated($request));

        return back()->with('success', 'Cor criada.');
    }

    public function update(Request $request, Color $color)
    {
        $color->update($this->validated($request));

        return back()->with('success', 'Cor atualizada.');
    }

    public function destroy(Color $color)
    {
        $color->delete();

        return back()->with('success', 'Cor excluída.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'color_group_id' => ['nullable', 'exists:color_groups,id'],
            'name' => ['required', 'string', 'max:160'],
            'code' => ['nullable', 'string', 'max:60'],
            'image_url' => ['nullable', 'url', 'max:255'],
        ]);
        $validated['color_group_id'] = $validated['color_group_id'] ?: null;
        $validated['active'] = $request->boolean('active');

        return $validated;
    }
}
