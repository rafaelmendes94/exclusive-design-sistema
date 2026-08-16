<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ColorGroup;
use Illuminate\Http\Request;

class ColorGroupController extends Controller
{
    public function index()
    {
        return view('admin.color-groups.index', [
            'groups' => ColorGroup::withCount('colors')->orderBy('name')->paginate(30),
        ]);
    }

    public function store(Request $request)
    {
        ColorGroup::create($this->validated($request));

        return back()->with('success', 'Grupo criado.');
    }

    public function update(Request $request, ColorGroup $group)
    {
        $group->update($this->validated($request));

        return back()->with('success', 'Grupo atualizado.');
    }

    public function destroy(ColorGroup $group)
    {
        $group->delete();

        return back()->with('success', 'Grupo excluído.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'image_url' => ['nullable', 'url', 'max:255'],
        ]);
        $validated['active'] = $request->boolean('active');

        return $validated;
    }
}
