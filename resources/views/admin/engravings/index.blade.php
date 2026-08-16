@extends('layouts.admin')

@section('title', 'Gravações')

@section('content')
    <div class="page-head">
        <div><h1>Gravações</h1><p>Tipos de gravação e preços por faixa de quantidade.</p></div>
        <a class="btn primary" href="{{ route('admin.engravings.create') }}">Incluir Novo</a>
    </div>
    <form class="filters" method="get">
        <input name="q" value="{{ request('q') }}" placeholder="Nome">
        <button class="btn">Buscar</button>
    </form>
    <table>
        <thead><tr><th>ID</th><th>Nome</th><th>Descrição</th><th>Faixas</th><th>Status</th><th>Clonar</th><th>Editar</th><th>Excluir</th></tr></thead>
        <tbody>
            @foreach($engravings as $engraving)
                <tr>
                    <td>{{ $engraving->id }}</td>
                    <td>{{ $engraving->name }}</td>
                    <td>{{ Str::limit($engraving->description, 80) }}</td>
                    <td>{{ $engraving->price_ranges_count }}</td>
                    <td><span class="badge">{{ $engraving->active ? 'Ativo' : 'Inativo' }}</span></td>
                    <td><form method="post" action="{{ route('admin.engravings.duplicate', $engraving) }}">@csrf<button class="btn small">Clonar</button></form></td>
                    <td><a class="btn small" href="{{ route('admin.engravings.edit', $engraving) }}">Editar</a></td>
                    <td><form method="post" action="{{ route('admin.engravings.destroy', $engraving) }}" onsubmit="return confirm('Excluir gravação?')">@csrf @method('delete')<button class="btn danger small">Excluir</button></form></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $engravings->links() }}
@endsection
