<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\FactorTable;
use App\Models\Engraving;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\QuoteStatus;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\QuotePriceCalculator;
use App\Support\VariationImageGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        $quotes = Quote::query()
            ->with(['status', 'customer'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->string('q')->toString();
                $query->where(function ($query) use ($q) {
                    $query->where('company', 'like', "%{$q}%")
                        ->orWhere('contact', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('id', $q);
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('quote_status_id', $request->integer('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.quotes.index', [
            'quotes' => $quotes,
            'statuses' => QuoteStatus::where('active', true)->orderBy('position')->get(),
        ]);
    }

    public function create(Request $request)
    {
        $quote = Quote::create([
            'quote_status_id' => QuoteStatus::orderBy('position')->value('id'),
            'seller' => $request->user()->role === 'seller' ? $request->user()->name : null,
            'origin' => 'Cadastro Adm',
        ]);

        return redirect()->route('admin.quotes.edit', $quote);
    }

    public function edit(Quote $quote)
    {
        $quote->load(['status', 'customer', 'items.product.engravings.priceRanges', 'items.variation', 'items.factorTable', 'items.engraving']);

        return view('admin.quotes.edit', [
            'quote' => $quote,
            'statuses' => QuoteStatus::where('active', true)->orderBy('position')->get(),
            'factorTables' => FactorTable::where('active', true)->orderBy('title')->get(),
            'engravings' => Engraving::where('active', true)->orderBy('name')->get(),
            'sellers' => User::where('role', 'seller')->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function proposal(Quote $quote)
    {
        return $this->proposalView($quote, false);
    }

    public function publicProposal(string $token)
    {
        $quote = Quote::where('public_token', $token)->firstOrFail();

        return $this->proposalView($quote, true);
    }

    public function update(Request $request, Quote $quote)
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'quote_status_id' => ['nullable', 'exists:quote_statuses,id'],
            'company' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:60'],
            'seller' => ['nullable', 'string', 'max:120'],
            'origin' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($request->user()->role === 'seller') {
            $validated['seller'] = $request->user()->name;
        }

        if (!empty($validated['customer_id'])) {
            $customer = Customer::find($validated['customer_id']);
            $validated['company'] = $customer?->company ?: $customer?->legal_name ?: $customer?->name;
            $validated['contact'] = $customer?->name;
            $validated['email'] = $customer?->email;
            $validated['phone'] = $customer?->commercial_phone ?: $customer?->mobile_phone ?: $customer?->home_phone;
            if ($request->user()->role !== 'seller') {
                $validated['seller'] = $validated['seller'] ?: $customer?->seller?->name;
            }
        } else {
            $validated['customer_id'] = null;
        }

        $quote->update($validated);

        return back()->with('success', 'Orçamento atualizado.');
    }

    public function duplicate(Quote $quote)
    {
        $quote->load('items');

        $newQuote = $quote->replicate(['proposal_sent_at']);
        $newQuote->proposal_sent_at = null;
        $newQuote->notes = trim(($quote->notes ? $quote->notes . "\n\n" : '') . "Duplicado do orçamento #{$quote->id}");
        $newQuote->created_at = now();
        $newQuote->updated_at = now();
        $newQuote->save();

        foreach ($quote->items as $item) {
            $newItem = $item->replicate();
            $newItem->quote_id = $newQuote->id;
            $newItem->created_at = now();
            $newItem->updated_at = now();
            $newItem->save();
        }

        return redirect()->route('admin.quotes.edit', $newQuote)->with('success', "Orçamento duplicado a partir do #{$quote->id}.");
    }

    public function quickStatus(Request $request, Quote $quote)
    {
        $validated = $request->validate([
            'quote_status_id' => ['required', 'exists:quote_statuses,id'],
        ]);

        $quote->update(['quote_status_id' => $validated['quote_status_id']]);

        return back()->with('success', 'Status do orçamento atualizado.');
    }

    public function share(Quote $quote)
    {
        if (!$quote->public_token) {
            do {
                $token = Str::random(48);
            } while (Quote::where('public_token', $token)->exists());

            $quote->update([
                'public_token' => $token,
                'proposal_sent_at' => now(),
            ]);
        }

        return back()->with('success', 'Link público da proposta gerado.');
    }

    public function searchCustomers(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $term = trim($validated['q'] ?? '');

        $customersQuery = Customer::query()
            ->with('seller:id,name')
            ->where('active', true);

        if (mb_strlen($term) >= 2) {
            $customersQuery
                ->where(function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('company', 'like', "%{$term}%")
                        ->orWhere('legal_name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('cnpj', 'like', "%{$term}%")
                        ->orWhere('cpf', 'like', "%{$term}%")
                        ->orWhere('commercial_phone', 'like', "%{$term}%")
                        ->orWhere('mobile_phone', 'like', "%{$term}%")
                        ->orWhere('home_phone', 'like', "%{$term}%");
                })
                ->orderBy('company')
                ->orderBy('name');
        } else {
            $customersQuery->latest();
        }

        $customers = $customersQuery
            ->limit(12)
            ->get();

        return response()->json($customers->map(fn (Customer $customer) => [
            'id' => $customer->id,
            'name' => $customer->name,
            'company' => $customer->company ?: $customer->legal_name ?: $customer->name,
            'email' => $customer->email,
            'phone' => $customer->commercial_phone ?: $customer->mobile_phone ?: $customer->home_phone,
            'document' => $customer->cnpj ?: $customer->cpf,
            'seller' => $customer->seller?->name,
        ])->values());
    }

    public function storeCustomer(Request $request, Quote $quote)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'commercial_phone' => ['nullable', 'string', 'max:80'],
            'cnpj' => ['nullable', 'string', 'max:40'],
        ]);

        $seller = $request->user()->role === 'seller'
            ? $request->user()
            : User::where('role', 'seller')->where('name', $quote->seller)->first();

        $customer = Customer::create([
            'active' => true,
            'seller_id' => $seller?->id,
            'name' => $validated['name'],
            'company' => $validated['company'] ?? null,
            'email' => $validated['email'] ?? null,
            'commercial_phone' => $validated['commercial_phone'] ?? null,
            'cnpj' => $validated['cnpj'] ?? null,
        ]);

        $quote->update([
            'customer_id' => $customer->id,
            'company' => $customer->company ?: $customer->name,
            'contact' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->commercial_phone,
            'seller' => $seller?->name ?: $quote->seller,
        ]);

        return back()->with('success', 'Cliente criado e vinculado ao orçamento.');
    }

    public function searchProducts(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $term = trim($validated['q'] ?? '');
        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $normalizedTerm = Str::of($term)->squish()->toString();
        $fullLike = "%{$normalizedTerm}%";
        $tokens = collect(preg_split('/\s+/', $normalizedTerm) ?: [])
            ->map(fn ($token) => trim($token))
            ->filter(fn ($token) => mb_strlen($token) >= 2)
            ->unique()
            ->take(8)
            ->values();
        $tokenVariants = $tokens->mapWithKeys(fn ($token) => [$token => $this->searchTokenVariants($token)]);
        $compactCode = Str::of($normalizedTerm)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9]/', '')
            ->toString();

        $variations = ProductVariation::query()
            ->with(['product:id,base_sku,supplier_product_id,supplier,name,description,image_url,sale_price,cost_price,status', 'primaryColor:id,name,hex', 'secondaryColor:id,name,hex'])
            ->where('status', 'active')
            ->whereHas('product')
            ->where(function ($query) use ($fullLike, $tokens, $tokenVariants, $compactCode) {
                $query->where(function ($query) use ($fullLike) {
                    $query->where('sku', 'like', $fullLike)
                        ->orWhere('supplier_variation_id', 'like', $fullLike)
                        ->orWhere('color', 'like', $fullLike)
                        ->orWhere('secondary_color', 'like', $fullLike)
                        ->orWhereHas('product', function ($query) use ($fullLike) {
                            $query->where('base_sku', 'like', $fullLike)
                                ->orWhere('supplier_product_id', 'like', $fullLike)
                                ->orWhere('supplier', 'like', $fullLike)
                                ->orWhere('name', 'like', $fullLike)
                                ->orWhere('description', 'like', $fullLike);
                        });
                });

                if ($tokens->isNotEmpty()) {
                    $query->orWhere(function ($query) use ($tokens, $tokenVariants) {
                        foreach ($tokens as $token) {
                            $query->where(function ($query) use ($tokenVariants, $token) {
                                foreach ($tokenVariants[$token] as $variant) {
                                    $like = "%{$variant}%";
                                    $query->orWhere('sku', 'like', $like)
                                        ->orWhere('supplier_variation_id', 'like', $like)
                                        ->orWhere('color', 'like', $like)
                                        ->orWhere('secondary_color', 'like', $like)
                                        ->orWhereHas('product', function ($query) use ($like) {
                                            $query->where('base_sku', 'like', $like)
                                                ->orWhere('supplier_product_id', 'like', $like)
                                                ->orWhere('supplier', 'like', $like)
                                                ->orWhere('name', 'like', $like)
                                                ->orWhere('description', 'like', $like);
                                        });
                                }
                            });
                        }
                    });
                }

                if ($compactCode !== '') {
                    $codeLike = "%{$compactCode}%";
                    $query->orWhereRaw("replace(replace(replace(upper(sku), '-', ''), '.', ''), ' ', '') like ?", [$codeLike])
                        ->orWhereRaw("replace(replace(replace(upper(coalesce(supplier_variation_id, '')), '-', ''), '.', ''), ' ', '') like ?", [$codeLike])
                        ->orWhereHas('product', function ($query) use ($codeLike) {
                            $query->whereRaw("replace(replace(replace(upper(base_sku), '-', ''), '.', ''), ' ', '') like ?", [$codeLike])
                                ->orWhereRaw("replace(replace(replace(upper(coalesce(supplier_product_id, '')), '-', ''), '.', ''), ' ', '') like ?", [$codeLike]);
                        });
                }
            })
            ->orderByRaw("
                case
                    when upper(sku) = upper(?) then 0
                    when exists (
                        select 1 from products
                        where products.id = product_variations.product_id
                        and upper(products.base_sku) = upper(?)
                    ) then 1
                    when sku like ? then 2
                    when exists (
                        select 1 from products
                        where products.id = product_variations.product_id
                        and products.name like ?
                    ) then 3
                    else 4
                end
            ", [$normalizedTerm, $normalizedTerm, $fullLike, $fullLike])
            ->orderByDesc('stock')
            ->orderBy('sku')
            ->limit(24)
            ->get();

        $user = $request->user();

        return response()->json($variations->map(function (ProductVariation $variation) use ($user) {
            $product = $variation->product;
            $price = $variation->sale_price ?: $product->sale_price ?: $variation->cost_price ?: $product->cost_price;

            return [
                'reference' => $variation->sku,
                'base_sku' => $product->base_sku,
                'name' => $product->name,
                'color' => trim(collect([$variation->color, $variation->secondary_color])->filter()->implode(' / ')),
                'stock' => $variation->stock,
                'image_url' => VariationImageGuard::safeImage($variation),
                'price' => $user->can_view_cost ? number_format((float) $price, 2, ',', '.') : null,
                'primary_hex' => $variation->primaryColor?->hex,
                'secondary_hex' => $variation->secondaryColor?->hex,
            ];
        })->values());
    }

    private function searchTokenVariants(string $token): array
    {
        $normalized = Str::of($token)->ascii()->lower()->toString();
        $variants = [''];
        $letters = [
            'a' => ['a', 'á', 'à', 'â', 'ã'],
            'e' => ['e', 'é', 'ê'],
            'i' => ['i', 'í'],
            'o' => ['o', 'ó', 'ô', 'õ'],
            'u' => ['u', 'ú'],
            'c' => ['c', 'ç'],
        ];

        foreach (mb_str_split($normalized) as $char) {
            $options = $letters[$char] ?? [$char];
            $next = [];

            foreach ($variants as $variant) {
                foreach ($options as $option) {
                    $next[] = $variant.$option;
                }
            }

            $variants = array_slice(array_values(array_unique($next)), 0, 24);
        }

        return array_values(array_unique(array_merge([$token, $normalized], $variants)));
    }

    public function addItem(Request $request, Quote $quote, QuotePriceCalculator $calculator)
    {
        $validated = $request->validate([
            'reference' => ['required', 'string'],
        ]);

        $reference = trim($validated['reference']);
        $variation = ProductVariation::with('product')
            ->where('sku', $reference)
            ->orWhereHas('product', fn ($query) => $query->where('base_sku', $reference))
            ->orderByDesc('stock')
            ->first();

        if (!$variation) {
            return back()->withErrors(['reference' => 'Referência/SKU não encontrado.']);
        }

        $product = $variation->product()->with('engravings.priceRanges')->first();
        $engraving = $product->engravings->first();
        $quantity = max((int) $product->minimum_quantity, 1);
        $engravingCost = $engraving ? $this->engravingCostFor($engraving, $quantity) : 0;
        $factorTableId = $product->factor_table_id ?: FactorTable::defaultMostExpensive()?->id;
        $calc = $calculator->calculate($product, $variation, $quantity, $factorTableId, 0, 0, 0, 0, $engravingCost);

        $quote->items()->create([
            'product_id' => $product->id,
            'product_variation_id' => $variation->id,
            'factor_table_id' => $factorTableId,
            'engraving_id' => $engraving?->id,
            'sku' => $variation->sku,
            'name' => $product->name,
            'image_url' => VariationImageGuard::safeImage($variation),
            'description' => $product->description,
            'quantity' => $quantity,
            'engraving' => $engraving?->name,
            'engraving_cost' => $engravingCost,
            'cost_price' => $variation->sale_price ?: $product->sale_price ?: $variation->cost_price ?: $product->cost_price,
            'unit_price' => $calc['unit_price'],
            'subtotal' => $calc['subtotal'],
            'calculation_snapshot' => ['proposal_1' => $calc],
        ]);

        return back()->with('success', 'Produto adicionado ao orçamento.');
    }

    public function updateItem(Request $request, Quote $quote, QuoteItem $item, QuotePriceCalculator $calculator)
    {
        abort_unless($item->quote_id === $quote->id, 404);

        $validated = $request->validate([
            'description' => ['nullable', 'string'],
            'factor_table_id' => ['nullable', 'exists:factor_tables,id'],
            'engraving_id' => ['nullable', 'exists:engravings,id'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'quantity_2' => ['nullable', 'integer', 'min:0'],
            'quantity_3' => ['nullable', 'integer', 'min:0'],
            'freight' => ['nullable', 'numeric', 'min:0'],
            'extra_percent' => ['nullable', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0'],
            'engraving' => ['nullable', 'string'],
            'engraving_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $product = $item->product()->with('engravings.priceRanges')->first();
        $variation = $item->variation;
        $engraving = null;
        if (!empty($validated['engraving_id'])) {
            $engraving = Engraving::with('priceRanges')->find($validated['engraving_id']);
            $validated['engraving'] = $engraving?->name;
        }
        $factorId = auth()->user()->can_view_factor ? (($validated['factor_table_id'] ?? null) ?: null) : $item->factor_table_id;
        $validated['factor_table_id'] = $factorId;
        $freight = (float) ($validated['freight'] ?? 0);
        $extra = (float) ($validated['extra_percent'] ?? 0);
        $tax = (float) ($validated['tax_percent'] ?? 0);
        $baseCost = (float) $item->cost_price;
        $snapshot = [];

        foreach ([1 => 'quantity', 2 => 'quantity_2', 3 => 'quantity_3'] as $idx => $field) {
            $qty = (int) ($validated[$field] ?? 0);
            $engravingCost = $engraving ? $this->engravingCostFor($engraving, $qty) : (float) ($validated['engraving_cost'] ?? 0);
            if ($idx === 1) {
                $validated['engraving_cost'] = $engravingCost;
            }
            $calc = $calculator->calculate($product, $variation, $qty, $factorId, $baseCost, $freight, $extra, $tax, $engravingCost);
            $snapshot["proposal_{$idx}"] = $calc;
            $validated[$idx === 1 ? 'unit_price' : "unit_price_{$idx}"] = $calc['unit_price'];
            $validated[$idx === 1 ? 'subtotal' : "subtotal_{$idx}"] = $calc['subtotal'];
        }

        $validated['calculation_snapshot'] = $snapshot;
        $item->update($validated);

        return back()->with('success', 'Item recalculado.');
    }

    public function destroyItem(Quote $quote, QuoteItem $item)
    {
        abort_unless($item->quote_id === $quote->id, 404);
        $item->delete();

        return back()->with('success', 'Item removido.');
    }

    private function engravingCostFor(Engraving $engraving, int $quantity): float
    {
        if ($quantity <= 0) {
            return 0;
        }

        $range = $engraving->priceRanges
            ->where('quantity_from', '<=', $quantity)
            ->filter(fn ($range) => $range->quantity_to === null || $range->quantity_to >= $quantity)
            ->sortByDesc('quantity_from')
            ->first();

        return $range ? (float) $range->price : 0;
    }

    private function proposalView(Quote $quote, bool $isPublic)
    {
        $quote->load(['status', 'customer', 'items.variation']);
        $proposalTotals = [];

        foreach ([1, 2, 3] as $idx) {
            $quantityField = $idx === 1 ? 'quantity' : "quantity_{$idx}";
            $subtotalField = $idx === 1 ? 'subtotal' : "subtotal_{$idx}";
            $total = 0;
            $hasQuantity = false;

            foreach ($quote->items as $item) {
                if ((int) $item->{$quantityField} > 0) {
                    $hasQuantity = true;
                    $total += (float) $item->{$subtotalField};
                }
            }

            if ($hasQuantity) {
                $proposalTotals[$idx] = $total;
            }
        }

        return view('admin.quotes.proposal', [
            'quote' => $quote,
            'proposalTotals' => $proposalTotals,
            'isPublic' => $isPublic,
            'settings' => SiteSetting::mapped(),
        ]);
    }
}
