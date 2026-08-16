@extends('layouts.admin')

@section('title', 'Fornecedores')

@section('content')
    <div class="page-head">
        <div>
            <h1>Fornecedores</h1>
            <p>Cadastro dos fornecedores usados no catálogo e integrações.</p>
        </div>
        <a class="btn primary" href="{{ route('admin.suppliers.create') }}">Incluir Novo</a>
    </div>

    <table>
        <thead><tr><th>Nome</th><th>Código</th><th>CNPJ</th><th>Integração</th><th>Contato</th><th>Status</th><th></th><th></th></tr></thead>
        <tbody>
            @foreach($suppliers as $supplier)
                <tr>
                    <td>{{ $supplier->name }}</td>
                    <td>{{ $supplier->code }}</td>
                    <td>{{ $supplier->cnpj }}</td>
                    <td><span class="badge">{{ $supplier->api_url ? 'API configurada' : 'Sem URL' }}</span></td>
                    <td>{{ $supplier->email ?: $supplier->phone }}</td>
                    <td><span class="badge">{{ $supplier->active ? 'Ativo' : 'Inativo' }}</span></td>
                    <td><a class="btn small" href="{{ route('admin.suppliers.edit', $supplier) }}">Editar</a></td>
                    <td><form method="post" action="{{ route('admin.suppliers.destroy', $supplier) }}" onsubmit="return confirm('Excluir fornecedor?')">@csrf @method('delete')<button class="btn danger small">Excluir</button></form></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $suppliers->links() }}
@endsection
