@extends('layouts.admin')

@section('title', 'Gravação')

@section('content')
    <div class="page-head">
        <div><h1>{{ $engraving->exists ? 'Editar Gravação' : 'Nova Gravação' }}</h1><p>Tabela de gravação com valores por faixa.</p></div>
        <a class="btn" href="{{ route('admin.engravings.index') }}">Voltar</a>
    </div>
    <form method="post" action="{{ $engraving->exists ? route('admin.engravings.update', $engraving) : route('admin.engravings.store') }}">
        @csrf
        @if($engraving->exists) @method('put') @endif
        <div class="form-grid">
            <label class="check"><input type="checkbox" name="active" value="1" @checked(old('active', $engraving->active))> Status Ativo</label>
            <label>Nome<input name="name" value="{{ old('name', $engraving->name) }}" required></label>
            <label class="wide">Descrição<textarea name="description" rows="4">{{ old('description', $engraving->description) }}</textarea></label>
        </div>
        <div class="section-title">Tabela de gravação com valores por faixa</div>
        <table>
            <thead><tr><th>Quantidade inicial</th><th>Quantidade final</th><th>Valor</th></tr></thead>
            <tbody>
                @php $ranges = old('ranges', $engraving->priceRanges?->toArray() ?: [['quantity_from' => '', 'quantity_to' => '', 'price' => '']]); @endphp
                @foreach($ranges as $idx => $range)
                    <tr>
                        <td><input type="number" name="ranges[{{ $idx }}][quantity_from]" value="{{ $range['quantity_from'] ?? '' }}"></td>
                        <td><input type="number" name="ranges[{{ $idx }}][quantity_to]" value="{{ $range['quantity_to'] ?? '' }}"></td>
                        <td><input name="ranges[{{ $idx }}][price]" value="{{ $range['price'] ?? '' }}"></td>
                    </tr>
                @endforeach
                @for($i = count($ranges); $i < count($ranges) + 6; $i++)
                    <tr>
                        <td><input type="number" name="ranges[{{ $i }}][quantity_from]"></td>
                        <td><input type="number" name="ranges[{{ $i }}][quantity_to]"></td>
                        <td><input name="ranges[{{ $i }}][price]"></td>
                    </tr>
                @endfor
            </tbody>
        </table>
        <div class="actions"><button class="btn primary">Salvar</button></div>
    </form>
@endsection
