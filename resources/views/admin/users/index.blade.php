@extends('layouts.admin')

@section('title', $title)

@section('content')
    <div class="page-head">
        <div><h1>{{ $title }}</h1><p>Cadastro de {{ $type === 'admins' ? 'usuários administrativos' : 'vendedores e permissões comerciais' }}.</p></div>
        <a class="btn primary" href="{{ route('admin.users.create', $type) }}">Incluir Novo</a>
    </div>

    <form class="filters" method="get">
        <input name="q" value="{{ request('q') }}" placeholder="Nome ou e-mail">
        <button class="btn">Buscar</button>
    </form>

    <table>
        <thead><tr><th>ID</th><th>Nome</th><th>Email</th><th>Fone comercial</th><th>Celular</th><th>Status</th><th>Permissões</th><th>Editar</th><th>Excluir</th></tr></thead>
        <tbody>
            @foreach ($users as $userRecord)
                <tr>
                    <td>{{ $userRecord->id }}</td>
                    <td>{{ $userRecord->name }}</td>
                    <td>{{ $userRecord->email }}</td>
                    <td>{{ $userRecord->phone }}</td>
                    <td>{{ $userRecord->mobile }}</td>
                    <td><span class="badge">{{ $userRecord->active ? 'Ativo' : 'Inativo' }}</span></td>
                    <td class="muted">
                        @if($userRecord->isSeller())
                            Forn: {{ $userRecord->can_view_supplier ? 'sim' : 'não' }} /
                            Custo: {{ $userRecord->can_view_cost ? 'sim' : 'não' }} /
                            Fator: {{ $userRecord->can_view_factor ? 'sim' : 'não' }}
                        @else
                            Admin
                        @endif
                    </td>
                    <td><a class="btn small" href="{{ route('admin.users.edit', [$type, $userRecord]) }}">Editar</a></td>
                    <td>
                        <form method="post" action="{{ route('admin.users.destroy', [$type, $userRecord]) }}" onsubmit="return confirm('Excluir cadastro?')">
                            @csrf @method('delete')
                            <button class="btn danger small">Excluir</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $users->links() }}
@endsection
