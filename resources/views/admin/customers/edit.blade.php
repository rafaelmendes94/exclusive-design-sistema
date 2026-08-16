@extends('layouts.admin')

@section('title', 'Cliente')

@section('content')
    <div class="page-head">
        <div><h1>{{ $customer->exists ? 'Editar Cliente' : 'Novo Cliente' }}</h1><p>Cadastro comercial e fiscal.</p></div>
        <a class="btn" href="{{ route('admin.customers.index') }}">Voltar</a>
    </div>

    <form class="form-grid" method="post" action="{{ $customer->exists ? route('admin.customers.update', $customer) : route('admin.customers.store') }}">
        @csrf
        @if($customer->exists) @method('put') @endif
        <label class="check"><input type="checkbox" name="active" value="1" @checked(old('active', $customer->active))> Ativo?</label>
        <label>Nome<input name="name" value="{{ old('name', $customer->name) }}"></label>
        <label>Empresa<input name="company" value="{{ old('company', $customer->company) }}"></label>
        <label>Razão Social<input name="legal_name" value="{{ old('legal_name', $customer->legal_name) }}"></label>
        <label>Inscrição Estadual<input name="state_registration" value="{{ old('state_registration', $customer->state_registration) }}"></label>
        <label>CNPJ<input name="cnpj" value="{{ old('cnpj', $customer->cnpj) }}"></label>
        <label>CPF<input name="cpf" value="{{ old('cpf', $customer->cpf) }}"></label>
        <label>Atendimento
            <select name="seller_id"><option value="">Sem vendedor</option>@foreach($sellers as $seller)<option value="{{ $seller->id }}" @selected($customer->seller_id===$seller->id)>{{ $seller->name }}</option>@endforeach</select>
        </label>
        <label>Ramo de atuação
            <select name="business_segment_id"><option value="">Sem ramo</option>@foreach($segments as $segment)<option value="{{ $segment->id }}" @selected($customer->business_segment_id===$segment->id)>{{ $segment->name }}</option>@endforeach</select>
        </label>
        <label>CEP<input name="zip" value="{{ old('zip', $customer->zip) }}"></label>
        <label>Logradouro<input name="street" value="{{ old('street', $customer->street) }}"></label>
        <label>Número<input name="number" value="{{ old('number', $customer->number) }}"></label>
        <label>Complemento<input name="complement" value="{{ old('complement', $customer->complement) }}"></label>
        <label>Bairro<input name="district" value="{{ old('district', $customer->district) }}"></label>
        <label>Cidade<input name="city" value="{{ old('city', $customer->city) }}"></label>
        <label>Estado<input maxlength="2" name="state" value="{{ old('state', $customer->state) }}"></label>
        <label>E-mail<input type="email" name="email" value="{{ old('email', $customer->email) }}"></label>
        <label>Telefone Comercial<input name="commercial_phone" value="{{ old('commercial_phone', $customer->commercial_phone) }}"></label>
        <label>Telefone Celular<input name="mobile_phone" value="{{ old('mobile_phone', $customer->mobile_phone) }}"></label>
        <label>Telefone Residencial<input name="home_phone" value="{{ old('home_phone', $customer->home_phone) }}"></label>
        <label class="wide">Observação<textarea name="notes" rows="4">{{ old('notes', $customer->notes) }}</textarea></label>
        <div class="wide actions"><button class="btn primary">Salvar</button></div>
    </form>

    @if($customer->exists)
        <div class="section-title">Histórico de orçamentos</div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Data</th>
                    <th>Status</th>
                    <th>Vendedor</th>
                    <th>Itens</th>
                    <th>Total principal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($quotes as $quote)
                    <tr>
                        <td>#{{ $quote->id }}</td>
                        <td>{{ $quote->created_at->format('d/m/Y') }}</td>
                        <td><span class="badge status-badge" style="--badge-color: {{ $quote->status?->color ?: '#777777' }}">{{ $quote->status?->name ?: 'Sem status' }}</span></td>
                        <td>{{ $quote->seller }}</td>
                        <td>{{ $quote->items_count }}</td>
                        <td>R$ {{ number_format((float) $quote->items->sum('subtotal'), 2, ',', '.') }}</td>
                        <td>
                            <div class="row-actions">
                                <a class="btn small" href="{{ route('admin.quotes.edit', $quote) }}">Abrir</a>
                                <a class="btn small" target="_blank" href="{{ route('admin.quotes.proposal', $quote) }}">Proposta</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">Nenhum orçamento vinculado a este cliente.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif
@endsection
