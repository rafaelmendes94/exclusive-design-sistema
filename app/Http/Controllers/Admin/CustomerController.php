<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSegment;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::query()
            ->with(['seller', 'businessSegment'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->string('q')->toString();
                $query->where(function ($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('company', 'like', "%{$q}%")
                        ->orWhere('cnpj', 'like', "%{$q}%");
                });
            })
            ->when($request->filled('seller_id'), fn ($query) => $query->where('seller_id', $request->integer('seller_id')))
            ->when($request->filled('business_segment_id'), fn ($query) => $query->where('business_segment_id', $request->integer('business_segment_id')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', [
            'customers' => $customers,
            'sellers' => User::where('role', 'seller')->orderBy('name')->get(),
            'segments' => BusinessSegment::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.customers.edit', $this->formData(new Customer(['active' => true])));
    }

    public function store(Request $request)
    {
        $customer = Customer::create($this->validated($request));

        return redirect()->route('admin.customers.edit', $customer)->with('success', 'Cliente criado.');
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', $this->formData($customer));
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validated($request));

        return back()->with('success', 'Cliente atualizado.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Cliente excluído.');
    }

    private function formData(Customer $customer): array
    {
        return [
            'customer' => $customer,
            'sellers' => User::where('role', 'seller')->orderBy('name')->get(),
            'segments' => BusinessSegment::orderBy('name')->get(),
            'quotes' => $customer->exists
                ? $customer->quotes()->with(['status', 'items:id,quote_id,subtotal'])->withCount('items')->latest()->limit(20)->get()
                : collect(),
        ];
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'seller_id' => ['nullable', 'exists:users,id'],
            'business_segment_id' => ['nullable', 'exists:business_segments,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'state_registration' => ['nullable', 'string', 'max:80'],
            'cnpj' => ['nullable', 'string', 'max:40'],
            'cpf' => ['nullable', 'string', 'max:40'],
            'zip' => ['nullable', 'string', 'max:20'],
            'street' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:30'],
            'complement' => ['nullable', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:2'],
            'email' => ['nullable', 'email', 'max:255'],
            'commercial_phone' => ['nullable', 'string', 'max:80'],
            'mobile_phone' => ['nullable', 'string', 'max:80'],
            'home_phone' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['active'] = $request->boolean('active');
        $validated['seller_id'] = $validated['seller_id'] ?: null;
        $validated['business_segment_id'] = $validated['business_segment_id'] ?: null;

        return $validated;
    }
}
