<article class="product-card" data-product-card data-code="{{ $product->base_sku }}" data-name="{{ $product->name }}" data-image="{{ $product->image_url }}">
    <a class="product-image" href="{{ route('site.product', ['codigo' => $product->base_sku]) }}">
        @if($product->image_url)
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
        @endif
    </a>
    <div class="product-info">
        <h3>{{ $product->name }}</h3>
        <div class="catalog-actions">
            <a href="{{ route('site.product', ['codigo' => $product->base_sku]) }}">Ver item</a>
            <button type="button" data-add-cart>Adicionar</button>
        </div>
    </div>
</article>
