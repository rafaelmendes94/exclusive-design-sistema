<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        return view('admin.suppliers.index', [
            'suppliers' => Supplier::orderBy('name')->paginate(20),
        ]);
    }

    public function create()
    {
        return view('admin.suppliers.edit', ['supplier' => new Supplier(['active' => true])]);
    }

    public function store(Request $request)
    {
        $supplier = Supplier::create($this->validated($request));

        return redirect()->route('admin.suppliers.edit', $supplier)->with('success', 'Fornecedor criado.');
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $supplier->update($this->validated($request, $supplier));

        return back()->with('success', 'Fornecedor atualizado.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('admin.suppliers.index')->with('success', 'Fornecedor excluído.');
    }

    private function validated(Request $request, ?Supplier $supplier = null): array
    {
        $id = $supplier?->id ?: 'NULL';
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:80', "unique:suppliers,code,{$id}"],
            'cnpj' => ['nullable', 'string', 'max:40'],
            'api_url' => ['nullable', 'url', 'max:255'],
            'api_key' => ['nullable', 'string'],
            'api_secret' => ['nullable', 'string'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['active'] = $request->boolean('active');

        if ($supplier?->exists) {
            if (!filled($validated['api_key'] ?? null)) {
                unset($validated['api_key']);
            }
            if (!filled($validated['api_secret'] ?? null)) {
                unset($validated['api_secret']);
            }
        }

        return $validated;
    }
}
