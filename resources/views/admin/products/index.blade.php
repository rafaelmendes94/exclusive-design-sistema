@extends('layouts.admin')

@section('title', 'Produtos')

@section('content')
    <div class="page-head">
        <div>
            <h1>Produtos</h1>
            <p>Produtos pai agrupados por referência, com variações vindas da XBZ.</p>
        </div>
    </div>

    <form class="filters" method="get">
        <input name="q" value="{{ request('q') }}" placeholder="Buscar por nome, referência ou código">
        <select name="status">
            <option value="">Todos os status</option>
            <option value="active" @selected(request('status') === 'active')>Ativo</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inativo</option>
        </select>
        <button class="btn">Filtrar</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Imagem</th><th>Referência</th><th>Produto</th>
                @if(auth()->user()->can_view_supplier)<th>Fornecedor</th>@endif
                @if(auth()->user()->can_view_cost)<th>Custo</th>@endif
                <th>Categoria</th>
                @if(auth()->user()->can_view_factor)<th>Fator</th>@endif
                <th>Variações</th><th>Status</th><th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr>
                    <td>@if($product->image_url)<img class="thumb" src="{{ $product->image_url }}" alt="" loading="lazy" decoding="async">@endif</td>
                    <td>{{ $product->base_sku }}</td>
                    <td>{{ $product->name }}</td>
                    @if(auth()->user()->can_view_supplier)<td>{{ $product->supplier }}</td>@endif
                    @if(auth()->user()->can_view_cost)
                    <td>
                        <form class="inline-form" method="post" action="{{ route('admin.products.quick-cost', $product) }}">
                            @csrf
                            @method('patch')
                            <input type="number" step="0.0001" name="cost_price" value="{{ $product->cost_price }}">
                            <button class="btn small">Salvar</button>
                        </form>
                    </td>
                    @endif
                    <td>{{ $product->category?->name ?: '-' }}</td>
                    @if(auth()->user()->can_view_factor)
                    <td>
                        <form class="inline-form" method="post" action="{{ route('admin.products.quick-factor', $product) }}">
                            @csrf
                            @method('patch')
                            <select name="factor_table_id">
                                <option value="">Sem fator</option>
                                @foreach($factorTables as $factorTable)
                                    <option value="{{ $factorTable->id }}" @selected($product->factor_table_id === $factorTable->id)>{{ $factorTable->title }}</option>
                                @endforeach
                            </select>
                            <button class="btn small">Salvar</button>
                        </form>
                    </td>
                    @endif
                    <td>{{ $product->variations_count }}</td>
                    <td>
                        <form class="inline-form" method="post" action="{{ route('admin.products.quick-status', $product) }}">
                            @csrf
                            @method('patch')
                            <select name="status">
                                <option value="active" @selected($product->status === 'active')>Ativo</option>
                                <option value="inactive" @selected($product->status === 'inactive')>Inativo</option>
                            </select>
                            <button class="btn small">Salvar</button>
                        </form>
                    </td>
                    <td><a class="btn small" href="{{ route('admin.products.edit', $product) }}">Editar</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $products->links() }}
@endsection
