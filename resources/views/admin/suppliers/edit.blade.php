@extends('layouts.admin')

@section('title', 'Fornecedor')

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ $supplier->exists ? 'Editar Fornecedor' : 'Novo Fornecedor' }}</h1>
            <p>Dados comerciais e técnicos do fornecedor.</p>
        </div>
        <a class="btn" href="{{ route('admin.suppliers.index') }}">Voltar</a>
    </div>

    <form class="form-grid" method="post" action="{{ $supplier->exists ? route('admin.suppliers.update', $supplier) : route('admin.suppliers.store') }}">
        @csrf
        @if($supplier->exists) @method('put') @endif
        <label class="check"><input type="checkbox" name="active" value="1" @checked(old('active', $supplier->active))> Ativo?</label>
        <label>Nome<input name="name" required value="{{ old('name', $supplier->name) }}"></label>
        <label>Código<input name="code" value="{{ old('code', $supplier->code) }}"></label>
        <label>CNPJ<input name="cnpj" value="{{ old('cnpj', $supplier->cnpj) }}"></label>
        <label>Contato<input name="contact_name" value="{{ old('contact_name', $supplier->contact_name) }}"></label>
        <label>E-mail<input type="email" name="email" value="{{ old('email', $supplier->email) }}"></label>
        <label>Telefone<input name="phone" value="{{ old('phone', $supplier->phone) }}"></label>
        <label class="wide">URL da API<input name="api_url" value="{{ old('api_url', $supplier->api_url) }}"></label>
        <label>Client ID / Chave<input name="api_key" value="{{ old('api_key') }}" placeholder="{{ $supplier->api_key ? 'Credencial cadastrada' : '' }}"></label>
        <label>Secret / Access Key<input type="password" name="api_secret" value="{{ old('api_secret') }}" placeholder="{{ $supplier->api_secret ? 'Credencial cadastrada' : '' }}"></label>
        <label class="wide">Observações<textarea name="notes" rows="4">{{ old('notes', $supplier->notes) }}</textarea></label>
        <div class="wide actions"><button class="btn primary">Salvar</button></div>
    </form>
@endsection
