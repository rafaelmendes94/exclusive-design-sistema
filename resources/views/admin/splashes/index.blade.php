@extends('layouts.admin')

@section('title', 'Splash')

@section('content')
    <div class="page-head"><div><h1>Splash</h1><p>Selos/etiquetas visuais para produtos.</p></div></div>
    <form class="filters filters-4" method="post" action="{{ route('admin.splashes.store') }}">
        @csrf
        <input name="name" placeholder="Nome">
        <input name="image_url" placeholder="Imagem URL">
        <label class="check"><input type="checkbox" name="active" value="1" checked> Ativo</label>
        <button class="btn primary">Incluir Novo</button>
    </form>
    <table>
        <thead><tr><th>ID</th><th>Nome</th><th>Imagem</th><th>Status</th><th>Salvar</th><th>Excluir</th></tr></thead>
        <tbody>
            @foreach($splashes as $splash)
                <tr>
                    <form method="post" action="{{ route('admin.splashes.update', $splash) }}">
                        @csrf @method('put')
                        <td>{{ $splash->id }}</td>
                        <td><input name="name" value="{{ $splash->name }}"></td>
                        <td><input name="image_url" value="{{ $splash->image_url }}"></td>
                        <td><label class="check"><input type="checkbox" name="active" value="1" @checked($splash->active)> Ativo</label></td>
                        <td><button class="btn small">Salvar</button></td>
                    </form>
                    <td><form method="post" action="{{ route('admin.splashes.destroy', $splash) }}" onsubmit="return confirm('Excluir splash?')">@csrf @method('delete')<button class="btn danger small">Excluir</button></form></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $splashes->links() }}
@endsection
