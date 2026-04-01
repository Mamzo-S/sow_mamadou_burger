<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Inscription - ISI Burger</title>
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
            <div class="col-lg-7 col-xl-6">
                <section class="hero-panel p-4 p-md-5 soft-card mb-4">
                    <h1 class="display-6 fw-semibold mb-0">Inscription</h1>
                </section>

                <div class="card soft-card border-0">
                    <div class="card-body p-4 p-md-5">
                        @if($errors->any())
                            <div class="alert alert-danger border-0 shadow-sm">
                                <strong>Veuillez corriger les erreurs du formulaire.</strong>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('register') }}" class="row g-3">
                            @csrf

                            <div class="col-md-6">
                                <label for="prenom" class="form-label">Prenom</label>
                                <input
                                    type="text"
                                    id="prenom"
                                    name="prenom"
                                    value="{{ old('prenom') }}"
                                    class="form-control @error('prenom') is-invalid @enderror"
                                    required
                                >
                                @error('prenom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="nom" class="form-label">Nom</label>
                                <input
                                    type="text"
                                    id="nom"
                                    name="nom"
                                    value="{{ old('nom') }}"
                                    class="form-control @error('nom') is-invalid @enderror"
                                    required
                                >
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="email" class="form-label">Email</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    class="form-control @error('email') is-invalid @enderror"
                                    required
                                >
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="telephone" class="form-label">Telephone</label>
                                <input
                                    type="text"
                                    id="telephone"
                                    name="telephone"
                                    value="{{ old('telephone') }}"
                                    class="form-control @error('telephone') is-invalid @enderror"
                                >
                                @error('telephone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="adresse" class="form-label">Adresse</label>
                                <input
                                    type="text"
                                    id="adresse"
                                    name="adresse"
                                    value="{{ old('adresse') }}"
                                    class="form-control @error('adresse') is-invalid @enderror"
                                >
                                @error('adresse')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
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

                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Confirmation du mot de passe</label>
                                <input
                                    type="password"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    class="form-control"
                                    required
                                >
                            </div>

                            <div class="col-12 d-grid gap-2 mt-3">
                                <button type="submit" class="btn btn-dark btn-lg">S'inscrire</button>
                                <a href="{{ route('login') }}" class="btn btn-outline-secondary">J'ai deja un compte</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
