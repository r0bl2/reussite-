<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion - Réussite+</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
</head>
<body class="login-page bg-body-secondary">
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center"><h1 class="mb-0">Réussite+</h1></div>
        <div class="card-body login-card-body">
            <form method="POST" action="{{ route('admin.login.submit') }}">
                @csrf
                <div class="mb-3">
                    <input type="text" name="username" value="{{ old('username') }}" class="form-control" placeholder="Identifiant" required autofocus>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Mot de passe" required>
                    <button type="button" class="btn btn-outline-secondary" id="togglePassword" aria-label="Afficher le mot de passe">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
                @error('username')
                    <div class="alert alert-danger py-2">{{ $message }}</div>
                @enderror
                <button type="submit" class="btn btn-primary w-100">Se connecter</button>
            </form>
        </div>
    </div>
</div>

<script>
    const pwd = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    document.getElementById('togglePassword').addEventListener('click', function () {
        const show = pwd.type === 'password';
        pwd.type = show ? 'text' : 'password';
        icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
</script>
</body>
</html>
