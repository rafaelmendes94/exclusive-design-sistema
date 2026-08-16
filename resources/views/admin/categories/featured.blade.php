@extends('layouts.admin')

@section('title', 'Categoria Selecionadas')

@section('content')
    <div class="page-head"><div><h1>Categoria Selecionadas</h1><p>Marque as categorias que terão destaque.</p></div></div>
    <form method="post" action="{{ route('admin.categories.featured.update') }}">
        @csrf @method('put')
        <table>
            <thead><tr><th>Selecionada</th><th>Categoria</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($categories as $category)
                    <tr>
                        <td><input type="checkbox" name="category_ids[]" value="{{ $category->id }}" @checked($category->featured)></td>
                        <td>{{ $category->name }}</td>
                        <td><span class="badge">{{ $category->active ? 'Ativo' : 'Inativo' }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="actions"><button class="btn primary">Salvar</button></div>
    </form>
@endsection
