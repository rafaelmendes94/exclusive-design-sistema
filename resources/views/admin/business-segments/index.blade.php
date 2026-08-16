@extends('layouts.admin')

@section('title', 'Ramo de Atuação')

@section('content')
    <div class="page-head"><div><h1>Ramo de Atuação</h1><p>Lista auxiliar usada no cadastro de clientes.</p></div></div>
    <form class="filters" method="post" action="{{ route('admin.segments.store') }}">
        @csrf
        <input name="name" placeholder="Novo ramo">
        <label class="check"><input type="checkbox" name="active" value="1" checked> Ativo</label>
        <button class="btn primary">Incluir Novo</button>
    </form>
    <table>
        <thead><tr><th>ID</th><th>Nome</th><th>Status</th><th>Salvar</th><th>Excluir</th></tr></thead>
        <tbody>
            @foreach($segments as $segment)
                <tr>
                    <form method="post" action="{{ route('admin.segments.update', $segment) }}">
                        @csrf @method('put')
                        <td>{{ $segment->id }}</td>
                        <td><input name="name" value="{{ $segment->name }}"></td>
                        <td><label class="check"><input type="checkbox" name="active" value="1" @checked($segment->active)> Ativo</label></td>
                        <td><button class="btn small">Salvar</button></td>
                    </form>
                    <td><form method="post" action="{{ route('admin.segments.destroy', $segment) }}" onsubmit="return confirm('Excluir ramo?')">@csrf @method('delete')<button class="btn danger small">Excluir</button></form></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $segments->links() }}
@endsection
