@extends('layouts.admin')

@section('title', 'Grupo de Cores')

@section('content')
    <div class="page-head"><div><h1>Grupo de Cores</h1><p>Agrupamento usado no cadastro de cores.</p></div></div>
    <form class="filters filters-4" method="post" action="{{ route('admin.color-groups.store') }}">
        @csrf
        <input name="name" placeholder="Nome">
        <input name="image_url" placeholder="Imagem URL">
        <label class="check"><input type="checkbox" name="active" value="1" checked> Ativo</label>
        <button class="btn primary">Incluir Novo</button>
    </form>
    <table>
        <thead><tr><th>ID</th><th>Nome</th><th>Imagem</th><th>Cores</th><th>Status</th><th>Salvar</th><th>Excluir</th></tr></thead>
        <tbody>
            @foreach($groups as $group)
                <tr>
                    <form method="post" action="{{ route('admin.color-groups.update', $group) }}">
                        @csrf @method('put')
                        <td>{{ $group->id }}</td>
                        <td><input name="name" value="{{ $group->name }}"></td>
                        <td><input name="image_url" value="{{ $group->image_url }}"></td>
                        <td>{{ $group->colors_count }}</td>
                        <td><label class="check"><input type="checkbox" name="active" value="1" @checked($group->active)> Ativo</label></td>
                        <td><button class="btn small">Salvar</button></td>
                    </form>
                    <td><form method="post" action="{{ route('admin.color-groups.destroy', $group) }}" onsubmit="return confirm('Excluir grupo?')">@csrf @method('delete')<button class="btn danger small">Excluir</button></form></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $groups->links() }}
@endsection
