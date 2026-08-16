<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Exclusive Design</title>
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body class="login-page">
    <form class="login-box" method="post" action="{{ route('login.store') }}">
        @csrf
        <h1>Exclusive Design</h1>
        <hr>
        @if ($errors->any())
            <div class="alert error">{{ $errors->first() }}</div>
        @endif
        <label>login<input type="email" name="email" value="{{ old('email') }}" autofocus required></label>
        <label>senha<input type="password" name="password" required></label>
        <label class="check"><input type="checkbox" name="remember" value="1"> Manter conectado</label>
        <button class="btn primary full">Entrar</button>
    </form>
</body>
</html>
