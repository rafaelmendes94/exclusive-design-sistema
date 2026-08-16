@extends('layouts.admin')

@section('title', 'Clientes')

@section('content')
    <div class="page-head">
        <div><h1>Lista de Clientes</h1><p>Clientes com representante, ramo de atuação e dados fiscais.</p></div>
        <a class="btn primary" href="{{ route('admin.customers.create') }}">Incluir Novo</a>
    </div>

    <form class="filters filters-4" method="get">
        <input name="q" value="{{ request('q') }}" placeholder="Nome, e-mail, empresa ou CNPJ">
        <select name="seller_id"><option value="">Vendedor</option>@foreach($sellers as $seller)<option value="{{ $seller->id }}" @selected((int)request('seller_id')===$seller->id)>{{ $seller->name }}</option>@endforeach</select>
        <select name="business_segment_id"><option value="">Ramo</option>@foreach($segments as $segment)<option value="{{ $segment->id }}" @selected((int)request('business_segment_id')===$segment->id)>{{ $segment->name }}</option>@endforeach</select>
        <button class="btn">Buscar</button>
    </form>

    <table>
        <thead><tr><th>ID</th><th>Nome</th><th>Email</th><th>Empresa</th><th>Fone comercial</th><th>Ramo</th><th>Vendedor</th><th>Status</th><th>Editar</th><th>Excluir</th></tr></thead>
        <tbody>
            @foreach($customers as $customer)
                <tr>
                    <td>{{ $customer->id }}</td>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->email }}</td>
                    <td>{{ $customer->company }}</td>
                    <td>{{ $customer->commercial_phone }}</td>
                    <td>{{ $customer->businessSegment?->name }}</td>
                    <td>{{ $customer->seller?->name }}</td>
                    <td><span class="badge">{{ $customer->active ? 'Ativo' : 'Inativo' }}</span></td>
                    <td><a class="btn small" href="{{ route('admin.customers.edit', $customer) }}">Editar</a></td>
                    <td><form method="post" action="{{ route('admin.customers.destroy', $customer) }}" onsubmit="return confirm('Excluir cliente?')">@csrf @method('delete')<button class="btn danger small">Excluir</button></form></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $customers->links() }}
@endsection
