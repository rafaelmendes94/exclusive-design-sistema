<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuoteStatus;
use Illuminate\Http\Request;

class QuoteStatusController extends Controller
{
    public function index()
    {
        return view('admin.quote-statuses.index', [
            'statuses' => QuoteStatus::orderBy('position')->get(),
        ]);
    }

    public function store(Request $request)
    {
        QuoteStatus::create($this->validated($request) + ['position' => QuoteStatus::max('position') + 1]);

        return back()->with('success', 'Status criado.');
    }

    public function update(Request $request, QuoteStatus $status)
    {
        $status->update($this->validated($request));

        return back()->with('success', 'Status atualizado.');
    }

    public function destroy(QuoteStatus $status)
    {
        $status->delete();

        return back()->with('success', 'Status excluído.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'color' => ['nullable', 'string', 'max:20'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);
        $validated['active'] = $request->boolean('active');

        return $validated;
    }
}
