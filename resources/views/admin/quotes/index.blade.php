@extends('layouts.admin')

@section('title', 'Orçamentos')

@section('content')
    <div class="page-head">
        <div>
            <h1>Orçamentos</h1>
            <p>Fluxo comercial com cliente, vendedor, produtos e três propostas por item.</p>
        </div>
        <form method="post" action="{{ route('admin.quotes.create') }}">@csrf<button class="btn primary">Novo orçamento</button></form>
    </div>

    <form class="filters" method="get">
        <input name="q" value="{{ request('q') }}" placeholder="Pedido, empresa, contato ou e-mail">
        <select name="status">
            <option value="">Todos os status</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->id }}" @selected((int) request('status') === $status->id)>{{ $status->name }}</option>
            @endforeach
        </select>
        <button class="btn">Filtrar</button>
    </form>

    <table>
        <thead><tr><th>ID</th><th>Data</th><th>Empresa</th><th>Contato</th><th>E-mail</th><th>Vendedor</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @foreach ($quotes as $quote)
                <tr>
                    <td>#{{ $quote->id }}</td>
                    <td>{{ $quote->created_at->format('d/m/Y') }}</td>
                    <td>{{ $quote->company }}</td>
                    <td>{{ $quote->contact }}</td>
                    <td>{{ $quote->email }}</td>
                    <td>{{ $quote->seller }}</td>
                    <td><span class="badge status-badge" style="--badge-color: {{ $quote->status?->color ?: '#777777' }}">{{ $quote->status?->name }}</span></td>
                    <td>
                        <div class="row-actions">
                            <a class="btn small" href="{{ route('admin.quotes.edit', $quote) }}">Editar</a>
                            <a class="btn small" target="_blank" href="{{ route('admin.quotes.proposal', $quote) }}">Proposta</a>
                            <form method="post" action="{{ route('admin.quotes.duplicate', $quote) }}">@csrf<button class="btn small">Duplicar</button></form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $quotes->links() }}
@endsection
