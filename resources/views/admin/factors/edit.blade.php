@extends('layouts.admin')

@section('title', 'Editar fator')

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ $factorTable->exists ? 'Editar fator' : 'Nova tabela de fator' }}</h1>
            <p>Fator produto por faixa. Regra: preço de venda = custo do produto x fator produto / 100.</p>
        </div>
        <a class="btn" href="{{ route('admin.factors.index') }}">Voltar</a>
    </div>

    <form method="post" action="{{ $factorTable->exists ? route('admin.factors.update', $factorTable) : route('admin.factors.store') }}">
        @csrf
        @if($factorTable->exists) @method('put') @endif

        <div class="form-grid">
            <label>Título<input name="title" value="{{ old('title', $factorTable->title) }}" required></label>
            <label class="check"><input type="checkbox" name="active" value="1" @checked($factorTable->active)> Ativo</label>
        </div>

        <div class="section-title">Faixas</div>
        <table>
            <thead><tr><th>Qtd. de</th><th>Qtd. até</th><th>Fator produto (%)</th></tr></thead>
            <tbody>
                @php $ranges = old('ranges', $factorTable->ranges?->toArray() ?: [['quantity_from' => 1, 'quantity_to' => 10000, 'product_factor_percent' => '212.7660']]); @endphp
                @foreach ($ranges as $idx => $range)
                    <tr>
                        <td><input type="number" name="ranges[{{ $idx }}][quantity_from]" value="{{ $range['quantity_from'] ?? '' }}"></td>
                        <td><input type="number" name="ranges[{{ $idx }}][quantity_to]" value="{{ $range['quantity_to'] ?? '' }}"></td>
                        <td><input name="ranges[{{ $idx }}][product_factor_percent]" value="{{ $range['product_factor_percent'] ?? '' }}"></td>
                    </tr>
                @endforeach
                @for ($i = count($ranges); $i < count($ranges) + 4; $i++)
                    <tr>
                        <td><input type="number" name="ranges[{{ $i }}][quantity_from]"></td>
                        <td><input type="number" name="ranges[{{ $i }}][quantity_to]"></td>
                        <td><input name="ranges[{{ $i }}][product_factor_percent]"></td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <div class="actions">
            <button class="btn primary" name="action" value="save">Salvar</button>
            @if($factorTable->exists)
                <button class="btn primary" name="action" value="update_products">Salvar & Atualizar Produtos</button>
            @endif
        </div>
    </form>
@endsection
