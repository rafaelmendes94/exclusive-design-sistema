@extends('layouts.site')

@section('title', $product->name . ' - ' . $settings['site_name'])

@section('content')
    @php
        $fallbackImage = $product->image_url;
        $gallery = collect([$fallbackImage])
            ->merge($product->variations->map(fn ($variation) => \App\Support\VariationImageGuard::safeImage($variation)))
            ->filter()
            ->unique()
            ->values();
        $selectedVariation = $product->variations->firstWhere('image_url') ?: $product->variations->first();
        $selectedImage = $selectedVariation ? \App\Support\VariationImageGuard::safeImage($selectedVariation) : $fallbackImage;
        $selectedImage = $selectedImage ?: $fallbackImage;
    @endphp

    <section class="product-detail">
        <div class="detail-gallery" data-product-gallery>
            <div class="detail-image">
                @if($selectedImage)
                    <img src="{{ $selectedImage }}" alt="{{ $product->name }}" data-main-product-image>
                @else
                    <span class="image-placeholder">Sem imagem</span>
                @endif
            </div>

            @if($gallery->count() > 1)
                <div class="detail-thumbs" aria-label="Galeria do produto">
                    @foreach($gallery->take(8) as $image)
                        <button
                            class="{{ $image === $selectedImage ? 'active' : '' }}"
                            type="button"
                            data-gallery-thumb
                            data-image="{{ $image }}"
                            aria-label="Ver imagem {{ $loop->iteration }}"
                        >
                            <img src="{{ $image }}" alt="" loading="lazy">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="detail-info">
            <span>{{ $product->category?->name ?: 'Produto' }}</span>
            <h1>{{ $product->name }}</h1>
            <p class="detail-code">Cód. <strong data-current-code>{{ $selectedVariation?->sku ?: $product->base_sku }}</strong></p>
            <p>{{ $product->description }}</p>
            <div class="variation-head">
                <strong>Variações</strong>
                <small data-current-variation>
                    {{ trim(collect([$selectedVariation?->color, $selectedVariation?->secondary_color])->filter()->implode(' / ')) ?: 'Selecione uma opção' }}
                </small>
            </div>
            <div class="variation-list" data-variation-list>
                @foreach($product->variations->take(24) as $variation)
                    @php
                        $variationImage = \App\Support\VariationImageGuard::safeImage($variation) ?: $fallbackImage;
                        $variationName = trim(collect([$variation->color, $variation->secondary_color])->filter()->implode(' / '));
                        $isSelected = $selectedVariation?->id === $variation->id;
                    @endphp
                    <button
                        class="{{ $isSelected ? 'active' : '' }}"
                        type="button"
                        data-variation-option
                        data-code="{{ $variation->sku }}"
                        data-name="{{ $product->name }}{{ $variationName ? ' - ' . $variationName : '' }}"
                        data-image="{{ $variationImage }}"
                        data-label="{{ $variationName ?: $variation->sku }}"
                    >
                        @if($variationImage)
                            <img src="{{ $variationImage }}" alt="" loading="lazy">
                        @else
                            <span class="variation-empty"></span>
                        @endif
                        <strong>{{ $variation->sku }}</strong>
                        <span>{{ $variationName ?: 'Sem cor definida' }}</span>
                        <small>Estoque {{ number_format($variation->stock, 0, ',', '.') }}</small>
                    </button>
                @endforeach
            </div>
            <article
                class="detail-cart"
                data-product-card
                data-code="{{ $selectedVariation?->sku ?: $product->base_sku }}"
                data-base-code="{{ $product->base_sku }}"
                data-name="{{ $product->name }}{{ $selectedVariation && trim(collect([$selectedVariation->color, $selectedVariation->secondary_color])->filter()->implode(' / ')) ? ' - ' . trim(collect([$selectedVariation->color, $selectedVariation->secondary_color])->filter()->implode(' / ')) : '' }}"
                data-base-name="{{ $product->name }}"
                data-image="{{ $selectedImage }}"
            >
                <button class="detail-cta" type="button" data-add-cart>Adicionar ao orçamento</button>
            </article>
        </div>
    </section>
@endsection
