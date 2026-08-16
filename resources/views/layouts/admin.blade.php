<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') - Exclusive Design</title>
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ route('admin.dashboard') }}">
            <img src="/logo-exclusive-white.png" alt="Exclusive Design">
        </a>
        <nav>
            <div class="nav-group"><span>Cadastros</span><div>
                <a href="{{ route('admin.users.index', 'admins') }}">Administrativos</a>
                <a href="{{ route('admin.customers.index') }}">Clientes</a>
                <a href="{{ route('admin.segments.index') }}">Ramo de Atuação</a>
                <a href="{{ route('admin.users.index', 'sellers') }}">Vendedores</a>
            </div></div>
            <div class="nav-group"><span>Preferências</span><div>
                <a href="{{ route('admin.factors.index') }}">Tabela de Fator</a>
                <a href="{{ route('admin.site-settings.edit') }}">CMS do Site</a>
                <a href="{{ route('admin.suppliers.index') }}">Fornecedores</a>
            </div></div>
            <div class="nav-group"><span>Produtos</span><div>
                <a href="{{ route('admin.products.index') }}">Catálogo de Produtos</a>
                <a href="{{ route('admin.categories.featured') }}">Categoria Selecionadas</a>
                <a href="{{ route('admin.categories.index') }}">Categorias</a>
                <a href="{{ route('admin.colors.index') }}">Cor</a>
                <a href="{{ route('admin.color-groups.index') }}">Grupo de Cores</a>
                <a href="{{ route('admin.engravings.index') }}">Gravações</a>
                <a href="{{ route('admin.splashes.index') }}">Splash</a>
            </div></div>
            <div class="nav-group"><span>Vendas</span><div>
                <a href="{{ route('admin.quotes.index') }}">Orçamentos</a>
                <a href="{{ route('admin.quote-statuses.index') }}">Status do Orçamento</a>
            </div></div>
        </nav>
        <div class="user-chip">{{ auth()->user()->name }}</div>
        <form method="post" action="{{ route('logout') }}" class="logout-form">@csrf<button>Sair</button></form>
    </header>

    <main class="page">
        @if (session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert error">{{ $errors->first() }}</div>
        @endif
        @yield('content')
    </main>
    @yield('scripts')
</body>
</html>
