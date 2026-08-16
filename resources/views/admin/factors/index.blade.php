@extends('layouts.admin')

@section('title', 'Tabela de fatores')

@section('content')
    <div class="page-head">
        <div>
            <h1>Tabela de fatores</h1>
            <p>Fator produto igual ao sistema Exclusive. A TAB.1 é a tabela padrão mais cara.</p>
        </div>
        <a class="btn primary" href="{{ route('admin.factors.create') }}">Nova tabela</a>
    </div>

    <table>
        <thead><tr><th>Título</th><th>Fator produto</th><th>Faixas</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @foreach ($factorTables as $factorTable)
                <tr>
                    <td>{{ $factorTable->title }}</td>
                    <td>
                        @if($factorTable->ranges->first())
                            {{ number_format($factorTable->ranges->first()->product_factor_percent, 4, ',', '.') }}%
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $factorTable->ranges_count }}</td>
                    <td><span class="badge">{{ $factorTable->active ? 'ativo' : 'inativo' }}</span></td>
                    <td class="row-actions">
                        <a class="btn small" href="{{ route('admin.factors.edit', $factorTable) }}">Editar</a>
                        <form method="post" action="{{ route('admin.factors.duplicate', $factorTable) }}">@csrf<button class="btn small">Duplicar</button></form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
