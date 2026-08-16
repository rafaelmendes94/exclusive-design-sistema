@extends('layouts.admin')

@section('title', 'Editar produto')

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ $product->name }}</h1>
            <p>{{ $product->base_sku }} - {{ $product->supplier }}</p>
        </div>
        <a class="btn" href="{{ route('admin.products.index') }}">Voltar</a>
    </div>

    <form class="form-grid" method="post" action="{{ route('admin.products.update', $product) }}">
        @csrf
        @method('put')

        <label>Nome<input name="name" value="{{ old('name', $product->name) }}" required></label>
        <label>Status
            <select name="status">
                <option value="active" @selected($product->status === 'active')>Ativo</option>
                <option value="inactive" @selected($product->status === 'inactive')>Inativo</option>
            </select>
        </label>
        <label>Categoria
            <select name="category_id">
                <option value="">Sem categoria</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($product->category_id === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </label>
        <label>Nova categoria<input name="category_name" placeholder="Criar categoria"></label>
        <label>Disponibilidade<input name="availability" value="{{ old('availability', $product->availability) }}"></label>
        <label>Splash
            <select name="splash_id">
                <option value="">Sem splash</option>
                @foreach ($splashes as $splash)
                    <option value="{{ $splash->id }}" @selected($product->splash_id === $splash->id)>{{ $splash->name }}</option>
                @endforeach
            </select>
        </label>
        @if(auth()->user()->can_view_factor)
        <label>Tabela de fator
            <select name="factor_table_id">
                <option value="">Sem fator</option>
                @foreach ($factorTables as $factorTable)
                    <option value="{{ $factorTable->id }}" @selected($product->factor_table_id === $factorTable->id)>{{ $factorTable->title }}</option>
                @endforeach
            </select>
        </label>
        @endif
        <label>Qtd. mínima<input type="number" min="1" name="minimum_quantity" value="{{ old('minimum_quantity', $product->minimum_quantity) }}"></label>
        <label>Info adicional<input name="additional_info" value="{{ old('additional_info', $product->additional_info) }}"></label>
        @if(auth()->user()->can_view_cost)
        <label>Custo fornecedor<input type="number" step="0.0001" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}"></label>
        <label>Preço base venda<input type="number" step="0.0001" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}"></label>
        @endif
        <label class="check"><input type="checkbox" name="block_supplier_update" value="1" @checked($product->block_supplier_update)> Bloquear atualização do fornecedor</label>
        <label class="check"><input type="checkbox" name="use_manual_price_table" value="1" @checked($product->use_manual_price_table)> Usar tabela de preço manual</label>
        <label>Altura<input type="number" step="0.001" name="height" value="{{ old('height', $product->height) }}"></label>
        <label>Largura<input type="number" step="0.001" name="width" value="{{ old('width', $product->width) }}"></label>
        <label>Profundidade<input type="number" step="0.001" name="depth" value="{{ old('depth', $product->depth) }}"></label>
        <label>Espessura<input type="number" step="0.001" name="thickness" value="{{ old('thickness', $product->thickness) }}"></label>
        <label>Comprimento<input type="number" step="0.001" name="length" value="{{ old('length', $product->length) }}"></label>
        <label>Circunferência<input type="number" step="0.001" name="circumference" value="{{ old('circumference', $product->circumference) }}"></label>
        <label>Diâmetro<input type="number" step="0.001" name="diameter" value="{{ old('diameter', $product->diameter) }}"></label>
        <label>Peso<input type="number" step="0.001" name="weight" value="{{ old('weight', $product->weight) }}"></label>
        <label>Energia<input name="energy" value="{{ old('energy', $product->energy) }}"></label>
        <label>Garantia<input name="warranty" value="{{ old('warranty', $product->warranty) }}"></label>
        <label>Medida gravação<input name="engraving_measure" value="{{ old('engraving_measure', $product->engraving_measure) }}"></label>
        <label>Tamanho total<input name="total_size" value="{{ old('total_size', $product->total_size) }}"></label>
        <label>Link YouTube<input name="youtube_url" value="{{ old('youtube_url', $product->youtube_url) }}"></label>
        <label class="check"><input type="checkbox" name="youtube_active" value="1" @checked($product->youtube_active)> Vídeo ativo</label>
        <label>SEO URL<input name="seo_url" value="{{ old('seo_url', $product->seo_url) }}"></label>
        <label>SEO Title<input name="seo_title" value="{{ old('seo_title', $product->seo_title) }}"></label>
        <label>SEO Keywords<input name="seo_keywords" value="{{ old('seo_keywords', $product->seo_keywords) }}"></label>
        <label>SEO Description<input name="seo_description" value="{{ old('seo_description', $product->seo_description) }}"></label>
        <label class="wide">Descrição<textarea name="description" rows="5">{{ old('description', $product->description) }}</textarea></label>
        <label class="wide">Informações técnicas<textarea name="technical_information" rows="4">{{ old('technical_information', $product->technical_information) }}</textarea></label>
        <label class="wide">Descrição de gravação<textarea name="engraving_description" rows="3">{{ old('engraving_description', $product->engraving_description) }}</textarea></label>
        <label class="wide">Descrição de refil<input name="refill_description" value="{{ old('refill_description', $product->refill_description) }}"></label>
        <div class="wide checkbox-grid">
            <strong>Gravações permitidas</strong>
            @foreach($engravings as $engraving)
                <label class="check"><input type="checkbox" name="engraving_ids[]" value="{{ $engraving->id }}" @checked($product->engravings->contains($engraving))> {{ $engraving->name }}</label>
            @endforeach
        </div>
        @if(auth()->user()->can_view_cost)
        <div class="wide">
            <div class="section-title">Tabela de preço manual</div>
            <table>
                <thead><tr><th>Quantidade inicial</th><th>Quantidade final</th><th>Preço</th></tr></thead>
                <tbody>
                    @php $manualPrices = old('manual_prices', $product->manualPriceRanges?->toArray() ?: [['quantity_from' => '', 'quantity_to' => '', 'price' => '']]); @endphp
                    @foreach($manualPrices as $idx => $range)
                        <tr>
                            <td><input type="number" name="manual_prices[{{ $idx }}][quantity_from]" value="{{ $range['quantity_from'] ?? '' }}"></td>
                            <td><input type="number" name="manual_prices[{{ $idx }}][quantity_to]" value="{{ $range['quantity_to'] ?? '' }}"></td>
                            <td><input name="manual_prices[{{ $idx }}][price]" value="{{ $range['price'] ?? '' }}"></td>
                        </tr>
                    @endforeach
                    @for($i = count($manualPrices); $i < count($manualPrices) + 6; $i++)
                        <tr>
                            <td><input type="number" name="manual_prices[{{ $i }}][quantity_from]"></td>
                            <td><input type="number" name="manual_prices[{{ $i }}][quantity_to]"></td>
                            <td><input name="manual_prices[{{ $i }}][price]"></td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
        @endif
        <div class="wide actions"><button class="btn primary">Salvar</button></div>
    </form>

    <div class="section-title">Variações</div>
    <table>
        <thead><tr><th>Imagem</th><th>SKU</th><th>Cor</th><th>Cor 2</th><th>Estoque</th><th>Estoque principal</th>@if(auth()->user()->can_view_cost)<th>Custo</th><th>Preço base</th>@endif<th>Status</th><th>Salvar</th></tr></thead>
        <tbody>
            @foreach ($product->variations as $variation)
                <tr>
                    <form method="post" action="{{ route('admin.products.variations.update', [$product, $variation]) }}">
                        @csrf
                        @method('put')
                        <td>
                            @if($variation->image_url)<img class="thumb" src="{{ $variation->image_url }}" alt="">@endif
                            <input name="image_url" value="{{ $variation->image_url }}" placeholder="Imagem URL">
                        </td>
                        <td>{{ $variation->sku }}</td>
                        <td>
                            <select name="color_id">
                                <option value="">Sem cor</option>
                                @foreach($colors as $color)
                                    <option value="{{ $color->id }}" @selected($variation->color_id === $color->id)>{{ $color->name }}</option>
                                @endforeach
                            </select>
                            <small class="muted">{{ $variation->color }}</small>
                        </td>
                        <td>
                            <select name="secondary_color_id">
                                <option value="">Sem cor</option>
                                @foreach($colors as $color)
                                    <option value="{{ $color->id }}" @selected($variation->secondary_color_id === $color->id)>{{ $color->name }}</option>
                                @endforeach
                            </select>
                            <small class="muted">{{ $variation->secondary_color }}</small>
                        </td>
                        <td><input type="number" name="stock" value="{{ $variation->stock }}"></td>
                        <td><input type="number" name="main_stock" value="{{ $variation->main_stock }}"></td>
                        @if(auth()->user()->can_view_cost)
                        <td><input type="number" step="0.0001" name="cost_price" value="{{ $variation->cost_price }}"></td>
                        <td><input type="number" step="0.0001" name="sale_price" value="{{ $variation->sale_price }}"></td>
                        @endif
                        <td>
                            <select name="status">
                                <option value="active" @selected($variation->status === 'active')>Ativo</option>
                                <option value="inactive" @selected($variation->status === 'inactive')>Inativo</option>
                            </select>
                        </td>
                        <td><button class="btn small">Salvar</button></td>
                    </form>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
