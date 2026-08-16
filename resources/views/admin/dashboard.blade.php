@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="dashboard-hero">
        <div>
            <img src="/logo-exclusive.png" alt="Exclusive Design">
            <p>Operação comercial, catálogo e propostas em um só painel.</p>
        </div>
        <div class="hero-actions">
            <form method="post" action="{{ route('admin.quotes.create') }}">
                @csrf
                <button class="btn primary">Novo orçamento</button>
            </form>
            <a class="btn" href="{{ route('admin.products.index') }}">Ver produtos</a>
            <a class="btn" href="{{ route('admin.customers.index') }}">Clientes</a>
        </div>
    </div>

    <section class="stats-grid dashboard-stats">
        <div class="stat"><span>Produtos ativos</span><strong>{{ number_format($activeProductsCount, 0, ',', '.') }}</strong><small>{{ number_format($productsCount, 0, ',', '.') }} no catálogo</small></div>
        <div class="stat"><span>Variações</span><strong>{{ number_format($variationsCount, 0, ',', '.') }}</strong><small>cores e SKUs do fornecedor</small></div>
        <div class="stat"><span>Orçamentos no mês</span><strong>{{ number_format($quotesThisMonth, 0, ',', '.') }}</strong><small>{{ number_format($approvedThisMonth, 0, ',', '.') }} aprovados</small></div>
        <div class="stat"><span>Clientes</span><strong>{{ number_format($customersCount, 0, ',', '.') }}</strong><small>cadastros comerciais</small></div>
    </section>

    <section class="shortcut-grid">
        <a class="shortcut" href="{{ route('admin.quotes.index') }}"><strong>Orçamentos</strong><span>Abrir propostas, duplicar, recalcular e gerar link público.</span></a>
        <a class="shortcut" href="{{ route('admin.products.index') }}"><strong>Catálogo</strong><span>Editar categoria, fator, status e dados vindos da XBZ.</span></a>
        <a class="shortcut" href="{{ route('admin.factors.index') }}"><strong>Tabela de fator</strong><span>Controlar multiplicadores usados nos preços de venda.</span></a>
        <a class="shortcut" href="{{ route('admin.site-settings.edit') }}"><strong>CMS do site</strong><span>Logo, textos, contato e WhatsApp do catálogo público.</span></a>
    </section>

    @if ($latestSync)
        <div class="notice dashboard-sync">
            <strong>Última importação XBZ:</strong> {{ $latestSync->status }} -
            {{ number_format($latestSync->items_received, 0, ',', '.') }} itens em
            {{ optional($latestSync->finished_at)->format('d/m/Y H:i') }}.
        </div>
    @endif

    <section class="columns dashboard-columns">
        <div>
            <div class="section-title">Funil de orçamentos</div>
            <div class="status-panel">
                @foreach ($quoteStatusCounts as $status)
                    <a class="status-row" href="{{ route('admin.quotes.index', ['status' => $status->id]) }}">
                        <span style="--status-color: {{ $status->color }}"></span>
                        <strong>{{ $status->name }}</strong>
                        <em>{{ number_format($status->quotes_count, 0, ',', '.') }}</em>
                    </a>
                @endforeach
            </div>

            <div class="section-title">Pendências do catálogo</div>
            <div class="todo-panel">
                <a href="{{ route('admin.products.index') }}">
                    <strong>{{ number_format($uncategorizedProductsCount, 0, ',', '.') }}</strong>
                    <span>produtos sem categoria</span>
                </a>
                <a href="{{ route('admin.categories.featured') }}">
                    <strong>Curadoria</strong>
                    <span>definir categorias e produtos de destaque no site</span>
                </a>
            </div>
        </div>

        <div>
            <div class="section-title">Orçamentos recentes</div>
            <table>
                <thead><tr><th>ID</th><th>Cliente</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @foreach ($latestQuotes as $quote)
                        <tr>
                            <td>#{{ $quote->id }}</td>
                            <td>{{ $quote->company ?: $quote->customer?->company ?: $quote->contact ?: 'Sem cliente' }}</td>
                            <td><span class="badge status-badge" style="--badge-color: {{ $quote->status?->color ?: '#777777' }}">{{ $quote->status?->name }}</span></td>
                            <td><a class="link" href="{{ route('admin.quotes.edit', $quote) }}">abrir</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="section-title">Produtos recentes</div>
            <table>
                <thead><tr><th>Referência</th><th>Produto</th><th>Variações</th><th></th></tr></thead>
                <tbody>
                    @foreach ($latestProducts as $product)
                        <tr>
                            <td>{{ $product->base_sku }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->variations_count }}</td>
                            <td><a class="link" href="{{ route('admin.products.edit', $product) }}">editar</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
