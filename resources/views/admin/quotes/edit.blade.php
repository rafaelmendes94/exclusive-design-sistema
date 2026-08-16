@extends('layouts.admin')

@section('title', 'Orçamento')

@section('content')
    <div class="page-head">
        <div>
            <h1>Orçamento #{{ $quote->id }}</h1>
            <p>{{ $quote->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div class="row-actions">
            <a class="btn primary" target="_blank" href="{{ route('admin.quotes.proposal', $quote) }}">Ver proposta</a>
            <form method="post" action="{{ route('admin.quotes.duplicate', $quote) }}">@csrf<button class="btn">Duplicar</button></form>
            <a class="btn" href="{{ route('admin.quotes.index') }}">Voltar</a>
        </div>
    </div>

    <div class="status-flow">
        @foreach ($statuses as $status)
            <form method="post" action="{{ route('admin.quotes.quick-status', $quote) }}">
                @csrf
                @method('patch')
                <input type="hidden" name="quote_status_id" value="{{ $status->id }}">
                <button class="status-step @if($quote->quote_status_id === $status->id) active @endif" style="--status-color: {{ $status->color }}">
                    {{ $status->name }}
                </button>
            </form>
        @endforeach
    </div>

    <div class="share-box">
        @if($quote->public_token)
            <label>Link público da proposta
                <input readonly value="{{ route('quotes.public', $quote->public_token) }}" onclick="this.select()">
            </label>
            <a class="btn" target="_blank" href="{{ route('quotes.public', $quote->public_token) }}">Abrir link</a>
        @else
            <form method="post" action="{{ route('admin.quotes.share', $quote) }}">
                @csrf
                <button class="btn primary">Gerar link público da proposta</button>
            </form>
        @endif
    </div>

    <form class="form-grid" method="post" action="{{ route('admin.quotes.update', $quote) }}">
        @csrf
        @method('put')
        <label class="wide">Cliente cadastrado
            <div class="customer-picker customer-picker-row">
                <input id="quote-customer-search" autocomplete="off" value="{{ $quote->customer ? (($quote->customer->company ?: $quote->customer->name) . ' #' . $quote->customer->id) : '' }}" placeholder="Buscar cliente por empresa, nome, e-mail, telefone ou documento">
                <button type="button" class="btn icon-btn" id="open-customer-modal" title="Cadastrar cliente">+</button>
                <input id="quote-customer-id" type="hidden" name="customer_id" value="{{ old('customer_id', $quote->customer_id) }}">
                <div id="quote-customer-results" class="customer-picker-results" hidden></div>
            </div>
        </label>
        <label>Status
            <select name="quote_status_id">
                @foreach ($statuses as $status)
                    <option value="{{ $status->id }}" @selected($quote->quote_status_id === $status->id)>{{ $status->name }}</option>
                @endforeach
            </select>
        </label>
        <label>Vendedor
            @if(auth()->user()->role === 'seller')
                <input value="{{ auth()->user()->name }}" readonly>
                <input type="hidden" name="seller" value="{{ auth()->user()->name }}">
            @else
                <select name="seller">
                    <option value="">Selecione o vendedor</option>
                    @foreach($sellers as $seller)
                        <option value="{{ $seller->name }}" @selected(old('seller', $quote->seller) === $seller->name)>{{ $seller->name }}</option>
                    @endforeach
                    @if($quote->seller && !$sellers->contains('name', $quote->seller))
                        <option value="{{ $quote->seller }}" selected>{{ $quote->seller }}</option>
                    @endif
                </select>
            @endif
        </label>
        <label>Empresa<input name="company" value="{{ old('company', $quote->company) }}"></label>
        <label>Contato<input name="contact" value="{{ old('contact', $quote->contact) }}"></label>
        <label>E-mail<input name="email" value="{{ old('email', $quote->email) }}"></label>
        <label>Telefone<input name="phone" value="{{ old('phone', $quote->phone) }}"></label>
        <div class="wide row-actions">
            <button type="button" class="btn small" id="clear-customer">Usar dados avulsos</button>
            @if($quote->customer)
                <a class="btn small" href="{{ route('admin.customers.edit', $quote->customer) }}">Abrir cliente</a>
            @endif
        </div>
        <label class="wide">Observações<textarea name="notes" rows="3">{{ old('notes', $quote->notes) }}</textarea></label>
        <div class="wide actions"><button class="btn primary">Salvar dados</button></div>
    </form>

    <div class="modal-backdrop" id="customer-modal-backdrop" hidden></div>
    <div class="modal" id="customer-modal" hidden>
        <div class="modal-dialog">
            <div class="modal-head">
                <div>
                    <strong>Cadastrar cliente</strong>
                    <p>Criar cliente e vincular neste orçamento.</p>
                </div>
                <button type="button" class="modal-close" id="close-customer-modal">×</button>
            </div>
            <form class="form-grid modal-form" method="post" action="{{ route('admin.quotes.customers.store', $quote) }}">
                @csrf
                <label>Nome do contato<input name="name" required></label>
                <label>Empresa<input name="company"></label>
                <label>E-mail<input type="email" name="email"></label>
                <label>Telefone<input name="commercial_phone"></label>
                <label>CNPJ<input name="cnpj"></label>
                <div class="wide actions"><button class="btn primary">Criar e vincular</button></div>
            </form>
        </div>
    </div>

    <div class="section-title">Adicionar produto</div>
    <form class="filters" method="post" action="{{ route('admin.quotes.items.add', $quote) }}">
        @csrf
        <div class="product-picker">
            <input id="quote-product-search" name="reference" autocomplete="off" placeholder="Digite referência, SKU, nome ou cor">
            <div id="quote-product-results" class="product-picker-results" hidden></div>
        </div>
        <button class="btn primary">Adicionar</button>
    </form>

    <div class="section-title">Produtos do orçamento</div>
    @foreach ($quote->items as $item)
        <form class="quote-item" method="post" action="{{ route('admin.quotes.items.update', [$quote, $item]) }}">
            @csrf
            @method('put')
            <div class="item-media">@if($item->image_url)<img src="{{ $item->image_url }}" alt="">@endif</div>
            <div class="item-main">
                <div class="item-head">
                    <strong>{{ $item->sku }} - {{ $item->name }}</strong>
                    <button form="delete-item-{{ $item->id }}" class="btn danger small">Remover</button>
                </div>
                <textarea name="description" rows="2">{{ $item->description }}</textarea>
                <div class="mini-grid">
                    @if(auth()->user()->can_view_factor)
                    <label>Fator
                        <select name="factor_table_id">
                            <option value="">Sem fator</option>
                            @foreach ($factorTables as $factorTable)
                                <option value="{{ $factorTable->id }}" @selected($item->factor_table_id === $factorTable->id)>{{ $factorTable->title }}</option>
                            @endforeach
                        </select>
                    </label>
                    @endif
                    <label>Frete<input type="number" step="0.01" name="freight" value="{{ $item->freight }}"></label>
                    <label>BV/outros %<input type="number" step="0.01" name="extra_percent" value="{{ $item->extra_percent }}"></label>
                    <label>Imposto %<input type="number" step="0.01" name="tax_percent" value="{{ $item->tax_percent }}"></label>
                    <label>Gravação
                        <select name="engraving_id">
                            <option value="">Sem gravação</option>
                            @php $availableEngravings = $item->product?->engravings?->isNotEmpty() ? $item->product->engravings : $engravings; @endphp
                            @foreach($availableEngravings as $engraving)
                                <option value="{{ $engraving->id }}" @selected($item->engraving_id === $engraving->id)>{{ $engraving->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    @if(auth()->user()->can_view_cost)
                        <label>Custo gravação<input type="number" step="0.01" name="engraving_cost" value="{{ $item->engraving_cost }}"></label>
                    @else
                        <input type="hidden" name="engraving_cost" value="{{ $item->engraving_cost }}">
                    @endif
                </div>
                <table>
                    <thead><tr><th>Proposta</th><th>Quantidade</th><th>Preço unitário</th><th>Subtotal</th>@if(auth()->user()->can_view_cost || auth()->user()->can_view_factor)<th>Memória</th>@endif</tr></thead>
                    <tbody>
                        @foreach ([1, 2, 3] as $idx)
                            @php
                                $qtyField = $idx === 1 ? 'quantity' : "quantity_{$idx}";
                                $priceField = $idx === 1 ? 'unit_price' : "unit_price_{$idx}";
                                $subtotalField = $idx === 1 ? 'subtotal' : "subtotal_{$idx}";
                                $memory = $item->calculation_snapshot["proposal_{$idx}"]['memory'] ?? '';
                            @endphp
                            <tr>
                                <td>{{ $idx }}</td>
                                <td><input type="number" min="0" name="{{ $qtyField }}" value="{{ $item->{$qtyField} }}"></td>
                                <td>R$ {{ number_format((float) $item->{$priceField}, 2, ',', '.') }}</td>
                                <td>R$ {{ number_format((float) $item->{$subtotalField}, 2, ',', '.') }}</td>
                                @if(auth()->user()->can_view_cost || auth()->user()->can_view_factor)
                                <td class="muted">{{ $memory }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="actions"><button class="btn primary">Recalcular item</button></div>
            </div>
        </form>
        <form id="delete-item-{{ $item->id }}" method="post" action="{{ route('admin.quotes.items.destroy', [$quote, $item]) }}">
            @csrf
            @method('delete')
        </form>
    @endforeach
@endsection

@section('scripts')
    <script>
        (() => {
            const customerInput = document.getElementById('quote-customer-search');
            const customerId = document.getElementById('quote-customer-id');
            const customerResults = document.getElementById('quote-customer-results');
            const clearCustomer = document.getElementById('clear-customer');
            const companyInput = document.querySelector('[name="company"]');
            const contactInput = document.querySelector('[name="contact"]');
            const emailInput = document.querySelector('[name="email"]');
            const phoneInput = document.querySelector('[name="phone"]');
            const sellerInput = document.querySelector('[name="seller"]');
            const openCustomerModal = document.getElementById('open-customer-modal');
            const closeCustomerModal = document.getElementById('close-customer-modal');
            const customerModal = document.getElementById('customer-modal');
            const customerModalBackdrop = document.getElementById('customer-modal-backdrop');
            const input = document.getElementById('quote-product-search');
            const results = document.getElementById('quote-product-results');

            let controller = null;
            let customerController = null;
            let timer = null;
            let customerTimer = null;

            const money = (value) => value ? `R$ ${value}` : '';
            const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
            })[char]);

            const renderCustomers = (items) => {
                if (!items.length) {
                    customerResults.innerHTML = '<div class="picker-empty">Nenhum cliente encontrado.</div>';
                    customerResults.hidden = false;
                    return;
                }

                customerResults.innerHTML = items.map((item) => `
                    <button type="button" class="customer-option" data-customer='${escapeHtml(JSON.stringify(item))}'>
                        <span>
                            <strong>${escapeHtml(item.company)}</strong>
                            <small>${escapeHtml(item.name || '')}${item.email ? ` · ${escapeHtml(item.email)}` : ''}${item.phone ? ` · ${escapeHtml(item.phone)}` : ''}</small>
                        </span>
                        <span>${escapeHtml(item.document || '')}</span>
                    </button>
                `).join('');
                customerResults.hidden = false;
            };

            const searchCustomers = () => {
                const q = customerInput.value.trim();
                if (customerController) customerController.abort();
                customerController = new AbortController();

                fetch(`{{ route('admin.quotes.customers.search') }}?q=${encodeURIComponent(q)}`, {
                    headers: { 'Accept': 'application/json' },
                    signal: customerController.signal,
                })
                    .then((response) => response.json())
                    .then(renderCustomers)
                    .catch((error) => {
                        if (error.name !== 'AbortError') {
                            customerResults.innerHTML = '<div class="picker-empty">Erro ao buscar clientes.</div>';
                            customerResults.hidden = false;
                        }
                    });
            };

            if (customerInput && customerResults) {
                customerInput.addEventListener('focus', searchCustomers);

                customerInput.addEventListener('input', () => {
                    customerId.value = '';
                    clearTimeout(customerTimer);
                    customerTimer = setTimeout(searchCustomers, 220);
                });

                customerResults.addEventListener('click', (event) => {
                    const option = event.target.closest('[data-customer]');
                    if (!option) return;
                    const customer = JSON.parse(option.dataset.customer);
                    customerId.value = customer.id;
                    customerInput.value = `${customer.company} #${customer.id}`;
                    companyInput.value = customer.company || '';
                    contactInput.value = customer.name || '';
                    emailInput.value = customer.email || '';
                    phoneInput.value = customer.phone || '';
                    if (customer.seller) sellerInput.value = customer.seller;
                    customerResults.hidden = true;
                });
            }

            const showCustomerModal = () => {
                customerModal.hidden = false;
                customerModalBackdrop.hidden = false;
                customerModal.querySelector('input[name="name"]').focus();
            };
            const hideCustomerModal = () => {
                customerModal.hidden = true;
                customerModalBackdrop.hidden = true;
            };

            if (openCustomerModal) openCustomerModal.addEventListener('click', showCustomerModal);
            if (closeCustomerModal) closeCustomerModal.addEventListener('click', hideCustomerModal);
            if (customerModalBackdrop) customerModalBackdrop.addEventListener('click', hideCustomerModal);
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && customerModal && !customerModal.hidden) hideCustomerModal();
            });

            if (clearCustomer) {
                clearCustomer.addEventListener('click', () => {
                    customerId.value = '';
                    customerInput.value = '';
                    customerInput.focus();
                });
            }

            if (!input || !results) return;

            const render = (items) => {
                if (!items.length) {
                    results.innerHTML = '<div class="picker-empty">Nenhum produto encontrado.</div>';
                    results.hidden = false;
                    return;
                }

                results.innerHTML = items.map((item) => {
                    const swatches = [item.primary_hex, item.secondary_hex]
                        .filter(Boolean)
                        .map((hex) => `<span class="swatch" style="background:${escapeHtml(hex)}"></span>`)
                        .join('');
                    const image = item.image_url
                        ? `<img src="${escapeHtml(item.image_url)}" alt="">`
                        : '<span class="picker-no-image"></span>';

                    return `
                        <button type="button" class="picker-option" data-reference="${escapeHtml(item.reference)}">
                            ${image}
                            <span>
                                <strong>${escapeHtml(item.reference)} - ${escapeHtml(item.name)}</strong>
                                <small>${escapeHtml(item.base_sku)}${item.color ? ` · ${escapeHtml(item.color)}` : ''} · Estoque ${escapeHtml(item.stock)}</small>
                            </span>
                            <span class="picker-side">${swatches}${money(item.price)}</span>
                        </button>
                    `;
                }).join('');
                results.hidden = false;
            };

            const search = () => {
                const q = input.value.trim();
                if (q.length < 2) {
                    results.hidden = true;
                    results.innerHTML = '';
                    return;
                }

                if (controller) controller.abort();
                controller = new AbortController();

                fetch(`{{ route('admin.quotes.products.search') }}?q=${encodeURIComponent(q)}`, {
                    headers: { 'Accept': 'application/json' },
                    signal: controller.signal,
                })
                    .then((response) => response.json())
                    .then(render)
                    .catch((error) => {
                        if (error.name !== 'AbortError') {
                            results.innerHTML = '<div class="picker-empty">Erro ao buscar produtos.</div>';
                            results.hidden = false;
                        }
                    });
            };

            input.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(search, 220);
            });

            results.addEventListener('click', (event) => {
                const option = event.target.closest('[data-reference]');
                if (!option) return;
                input.value = option.dataset.reference;
                results.hidden = true;
                input.closest('form').requestSubmit();
            });

            document.addEventListener('click', (event) => {
                if (!event.target.closest('.product-picker')) {
                    results.hidden = true;
                }
                if (!event.target.closest('.customer-picker')) {
                    customerResults.hidden = true;
                }
            });
        })();
    </script>
@endsection
