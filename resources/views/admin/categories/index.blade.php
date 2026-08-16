@extends('layouts.admin')

@section('title', 'Categorias')

@section('content')
    <div class="page-head">
        <div><h1>Lista</h1><p>Categorias e subcategorias do catálogo.</p></div>
        <a class="btn primary" href="{{ route('admin.categories.create') }}">Incluir Novo</a>
    </div>

    <form class="filters" method="get">
        <input name="q" value="{{ request('q') }}" placeholder="Nome">
        <select name="status">
            <option value="">Todos</option>
            <option value="active" @selected(request('status') === 'active')>Ativo</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inativo</option>
        </select>
        <button class="btn">Buscar</button>
    </form>

    <table>
        <thead><tr><th>ID</th><th>Nome</th><th>Pai</th><th>Menu</th><th>Fator</th><th>Destaque</th><th>Status</th><th>Editar</th><th>Excluir</th></tr></thead>
        <tbody>
            @foreach($categories as $category)
                <tr>
                    <td>{{ $category->id }}</td>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->parent?->name }}</td>
                    <td>{{ $category->show_in_menu ? 'Sim' : 'Não' }}</td>
                    <td>{{ $category->categoryFactorTable?->title }}</td>
                    <td>{{ $category->featured ? 'Sim' : 'Não' }}</td>
                    <td><span class="badge">{{ $category->active ? 'Ativo' : 'Inativo' }}</span></td>
                    <td><a class="btn small" href="{{ route('admin.categories.edit', $category) }}">Editar</a></td>
                    <td><form method="post" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Excluir categoria?')">@csrf @method('delete')<button class="btn danger small">Excluir</button></form></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $categories->links() }}
@endsection
