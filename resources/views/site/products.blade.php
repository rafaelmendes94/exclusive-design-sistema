@extends('layouts.site')

@section('title', 'Produtos - ' . $settings['site_name'])

@section('content')
    <section class="listing-hero">
        <span>Produtos</span>
        <h1>Catálogo de brindes personalizados</h1>
        <p>Busque por nome, código, SKU ou categoria.</p>
    </section>

    <form class="site-filters" method="get">
        <input name="q" value="{{ request('q') }}" placeholder="Nome, código ou SKU">
        <select name="category_id">
            <option value="">Todas categorias</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((int) request('category_id') === $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <button>Aplicar filtros</button>
        <a href="{{ route('site.products') }}">Limpar</a>
    </form>

    <section class="site-section">
        <div class="product-grid">
            @foreach($products as $product)
                @include('site.partials.product-card', ['product' => $product])
            @endforeach
        </div>
        {{ $products->links() }}
    </section>
@endsection
