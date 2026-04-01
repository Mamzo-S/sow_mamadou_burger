<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion - ISI Burger</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(180deg, #fff6ea 0%, #f7f7f7 100%);
        }

        .auth-shell {
            min-height: 100vh;
        }

        .hero-panel {
            background: linear-gradient(135deg, #212529 0%, #5c2f14 100%);
            color: #fff;
            border-radius: 1.5rem;
        }

        .soft-card {
            border: 0;
            border-radius: 1.25rem;
            box-shadow: 0 1rem 2.5rem rgba(0, 0, 0, 0.08);
        }
    </style>
</head>
<body>
    <main class="container auth-shell d-flex align-items-center py-5">
        <div class="row justify-content-center w-100">
            <div class="col-lg-6 col-xl-5">
                <section class="hero-panel p-4 p-md-5 soft-card mb-4">
                    <h1 class="display-6 fw-semibold mb-0">Connexion</h1>
                </section>

                <div class="card soft-card border-0">
                    <div class="card-body p-4 p-md-5">
                        @if(session('status'))
                            <div class="alert alert-success border-0 shadow-sm">{{ session('status') }}</div>
                        @endif

                        @if(session('success'))
                            <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger border-0 shadow-sm">
                                <strong>Email ou mot de passe incorrect.</strong>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" class="d-grid gap-3">
                            @csrf

                            <div>
                                <label for="email" class="form-label">Email</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    class="form-control @error('email') is-invalid @enderror"
                                    required
                                    autofocus
                                >
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="form-label">Mot de passe</label>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    required
                                >
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Se souvenir de moi
                                </label>
                            </div>

                            <button type="submit" class="btn btn-dark btn-lg">Se connecter</button>
                        </form>

                        <div class="text-center mt-4">
                            <a href="{{ route('password.request') }}" class="d-inline-block mb-3 text-decoration-none">Mot de passe oublie ?</a>
                        </div>

                        <div class="text-center">
                            <div class="text-muted mb-2">Vous n'avez pas encore de compte ?</div>
                            <a href="{{ route('register') }}" class="btn btn-outline-dark">Creer un compte client</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
