@extends('layouts.admin')

@section('title', 'Status do Orçamento')

@section('content')
    <div class="page-head"><div><h1>Status do Orçamento</h1><p>Status editáveis para o fluxo comercial.</p></div></div>
    <form class="filters filters-4" method="post" action="{{ route('admin.quote-statuses.store') }}">
        @csrf
        <input name="name" placeholder="Nome do Status">
        <input name="color" value="#64748b" placeholder="Cor">
        <input type="number" name="position" placeholder="Ordem">
        <button class="btn primary">Incluir Novo</button>
    </form>
    <table>
        <thead><tr><th>Nome</th><th>Cor</th><th>Ordem</th><th>Status</th><th>Salvar</th><th>Excluir</th></tr></thead>
        <tbody>
            @foreach($statuses as $status)
                <tr>
                    <form method="post" action="{{ route('admin.quote-statuses.update', $status) }}">
                        @csrf @method('put')
                        <td><input name="name" value="{{ $status->name }}"></td>
                        <td><input name="color" value="{{ $status->color }}"></td>
                        <td><input type="number" name="position" value="{{ $status->position }}"></td>
                        <td><label class="check"><input type="checkbox" name="active" value="1" @checked($status->active)> Ativo</label></td>
                        <td><button class="btn small">Salvar</button></td>
                    </form>
                    <td><form method="post" action="{{ route('admin.quote-statuses.destroy', $status) }}" onsubmit="return confirm('Excluir status?')">@csrf @method('delete')<button class="btn danger small">Excluir</button></form></td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
