@extends('layouts.site')

@section('title', $settings['site_name'])

@section('content')
    <section class="hero">
        <img src="{{ $settings['hero_image'] }}" alt="{{ $settings['hero_title'] }}">
        <div class="hero-content">
            <span>{{ $settings['hero_badge'] }}</span>
            <h1>{{ $settings['hero_title'] }}</h1>
            <p>{{ $settings['hero_text'] }}</p>
            <a href="{{ route('site.products') }}">Ver itens</a>
        </div>
    </section>

    @if($categories->isNotEmpty())
        <section class="category-strip">
            <strong>Todas Categorias</strong>
            <div>
                @foreach($categories as $category)
                    <a href="{{ route('site.products', ['category_id' => $category->id]) }}">{{ $category->name }}</a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="site-section">
        <div class="section-head">
            <span>Curadoria</span>
            <h2>Produtos em destaque</h2>
        </div>
        <div class="product-row">
            @foreach($featuredProducts as $product)
                @include('site.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </section>

    <section class="category-showcase" id="catalogos">
        <div class="section-head">
            <span>Categorias</span>
            <h2>Veja algumas categorias</h2>
        </div>
        <div class="showcase-grid">
            @forelse($featuredCategories as $category)
                <a href="{{ route('site.products', ['category_id' => $category->id]) }}">
                    <img src="{{ $category->banner_desktop ?: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1400&q=80' }}" alt="{{ $category->name }}">
                    <strong>{{ $category->name }}</strong>
                </a>
            @empty
                <a href="{{ route('site.products') }}">
                    <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1400&q=80" alt="Mochilas corporativas">
                    <strong>Mochilas corporativas</strong>
                </a>
                <a href="{{ route('site.products') }}">
                    <img src="https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=1400&q=80" alt="Camisetas promocionais">
                    <strong>Camisetas promocionais</strong>
                </a>
                <a href="{{ route('site.products') }}">
                    <img src="https://images.unsplash.com/photo-1523362628745-0c100150b504?auto=format&fit=crop&w=1400&q=80" alt="Squeezes e garrafas">
                    <strong>Squeezes e garrafas</strong>
                </a>
            @endforelse
        </div>
    </section>

    <section class="site-section">
        <div class="section-head">
            <span>Novidades</span>
            <h2>Últimos produtos cadastrados</h2>
        </div>
        <div class="product-grid">
            @foreach($newProducts as $product)
                @include('site.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </section>

    <section class="about-band" id="sobre">
        <div>
            <span>Atendimento consultivo</span>
            <h2>Brindes corporativos com curadoria, personalização e presença de marca.</h2>
            <p>{{ $settings['meta_description'] }}</p>
        </div>
    </section>
@endsection
