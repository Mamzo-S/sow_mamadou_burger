<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mot de passe oublie - ISI Burger</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="min-height: 100vh; background: linear-gradient(180deg, #fff6ea 0%, #f7f7f7 100%);">
    <main class="container d-flex align-items-center py-5" style="min-height: 100vh;">
        <div class="row justify-content-center w-100">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <h1 class="h3 mb-3">Mot de passe oublie</h1>
                        <p class="text-muted">Saisissez votre email pour recevoir un lien de reinitialisation.</p>

                        @if(session('status'))
                            <div class="alert alert-success">{{ session('status') }}</div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}" class="d-grid gap-3">
                            @csrf
                            <div>
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-dark">Envoyer le lien</button>
                            <a href="{{ route('login') }}" class="btn btn-outline-secondary">Retour a la connexion</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
