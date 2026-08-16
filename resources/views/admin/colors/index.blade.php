@extends('layouts.admin')

@section('title', 'Cor')

@section('content')
    <div class="page-head"><div><h1>Cor</h1><p>Cores usadas nas variações dos produtos.</p></div></div>
    <form class="filters filters-4" method="get">
        <input name="q" value="{{ request('q') }}" placeholder="Nome ou código">
        <select name="group"><option value="">Grupo</option>@foreach($groups as $group)<option value="{{ $group->id }}" @selected((int)request('group')===$group->id)>{{ $group->name }}</option>@endforeach</select>
        <button class="btn">Buscar</button>
    </form>
    <form class="filters filters-4" method="post" action="{{ route('admin.colors.store') }}">
        @csrf
        <input name="name" placeholder="Nome">
        <input name="code" placeholder="Código">
        <select name="color_group_id"><option value="">Grupo</option>@foreach($groups as $group)<option value="{{ $group->id }}">{{ $group->name }}</option>@endforeach</select>
        <button class="btn primary">Incluir Novo</button>
        <input class="wide" name="image_url" placeholder="Imagem URL">
    </form>
    <table>
        <thead><tr><th>ID</th><th>Nome</th><th>Código</th><th>Imagem</th><th>Grupo</th><th>Status</th><th>Salvar</th><th>Excluir</th></tr></thead>
        <tbody>
            @foreach($colors as $color)
                <tr>
                    <form method="post" action="{{ route('admin.colors.update', $color) }}">
                        @csrf @method('put')
                        <td>{{ $color->id }}</td>
                        <td><input name="name" value="{{ $color->name }}"></td>
                        <td><input name="code" value="{{ $color->code }}"></td>
                        <td><input name="image_url" value="{{ $color->image_url }}"></td>
                        <td><select name="color_group_id"><option value="">Grupo</option>@foreach($groups as $group)<option value="{{ $group->id }}" @selected($color->color_group_id===$group->id)>{{ $group->name }}</option>@endforeach</select></td>
                        <td><label class="check"><input type="checkbox" name="active" value="1" @checked($color->active)> Ativo</label></td>
                        <td><button class="btn small">Salvar</button></td>
                    </form>
                    <td><form method="post" action="{{ route('admin.colors.destroy', $color) }}" onsubmit="return confirm('Excluir cor?')">@csrf @method('delete')<button class="btn danger small">Excluir</button></form></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $colors->links() }}
@endsection
