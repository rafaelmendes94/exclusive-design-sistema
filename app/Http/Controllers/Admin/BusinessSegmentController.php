<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSegment;
use Illuminate\Http\Request;

class BusinessSegmentController extends Controller
{
    public function index()
    {
        return view('admin.business-segments.index', [
            'segments' => BusinessSegment::orderBy('name')->paginate(30),
        ]);
    }

    public function store(Request $request)
    {
        BusinessSegment::create($this->validated($request));

        return back()->with('success', 'Ramo criado.');
    }

    public function update(Request $request, BusinessSegment $segment)
    {
        $segment->update($this->validated($request));

        return back()->with('success', 'Ramo atualizado.');
    }

    public function destroy(BusinessSegment $segment)
    {
        $segment->delete();

        return back()->with('success', 'Ramo excluído.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
        ]);
        $validated['active'] = $request->boolean('active');

        return $validated;
    }
}
