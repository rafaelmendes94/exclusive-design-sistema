@extends('layouts.admin')

@section('title', $title)

@section('content')
    <div class="page-head">
        <div><h1>{{ $userRecord->exists ? 'Editar '.$title : 'Novo '.$title }}</h1><p>Campos baseados no cadastro do Clic.</p></div>
        <a class="btn" href="{{ route('admin.users.index', $type) }}">Voltar</a>
    </div>

    <form class="form-grid" method="post" action="{{ $userRecord->exists ? route('admin.users.update', [$type, $userRecord]) : route('admin.users.store', $type) }}">
        @csrf
        @if($userRecord->exists) @method('put') @endif

        <label class="check"><input type="checkbox" name="active" value="1" @checked(old('active', $userRecord->active))> Ativo?</label>
        @if($type === 'sellers')
            <label class="check"><input type="checkbox" name="default_seller" value="1" @checked(old('default_seller', $userRecord->default_seller))> Vendedor padrão</label>
            <label class="check"><input type="checkbox" name="can_view_supplier" value="1" @checked(old('can_view_supplier', $userRecord->can_view_supplier))> Ver fornecedor?</label>
            <label class="check"><input type="checkbox" name="can_view_cost" value="1" @checked(old('can_view_cost', $userRecord->can_view_cost))> Ver custo?</label>
            <label class="check"><input type="checkbox" name="can_view_factor" value="1" @checked(old('can_view_factor', $userRecord->can_view_factor))> Ver fator?</label>
        @endif
        <label>Nome<input name="name" value="{{ old('name', $userRecord->name) }}" required></label>
        <label>E-mail<input type="email" name="email" value="{{ old('email', $userRecord->email) }}" required></label>
        <label>Senha<input type="password" name="password" placeholder="{{ $userRecord->exists ? 'Manter atual' : '' }}"></label>
        <label>Telefone<input name="phone" value="{{ old('phone', $userRecord->phone) }}"></label>
        <label>Celular/Whatsapp<input name="mobile" value="{{ old('mobile', $userRecord->mobile) }}"></label>
        <label>RG<input name="rg" value="{{ old('rg', $userRecord->rg) }}"></label>
        <label>Órgão emissor RG<input name="rg_issuer" value="{{ old('rg_issuer', $userRecord->rg_issuer) }}"></label>
        <label>CPF<input name="cpf" value="{{ old('cpf', $userRecord->cpf) }}"></label>
        @if($type === 'sellers')
            <label>Nome da Empresa<input name="company" value="{{ old('company', $userRecord->company) }}"></label>
            <label>Nome Fantasia<input name="trade_name" value="{{ old('trade_name', $userRecord->trade_name) }}"></label>
            <label>CNPJ<input name="cnpj" value="{{ old('cnpj', $userRecord->cnpj) }}"></label>
        @endif
        <div class="wide actions"><button class="btn primary">Salvar</button></div>
    </form>
@endsection
