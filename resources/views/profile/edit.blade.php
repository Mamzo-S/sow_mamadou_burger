@extends('layouts.app')

@section('title', 'Mon profil')

@section('content')
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card soft-card border-0 h-100">
                <div class="card-body p-4">
                    <h2 class="h4 mb-3">Mes informations</h2>

                    @if(session('status') === 'profile-updated')
                        <div class="alert alert-success">Informations mises a jour.</div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}" class="d-grid gap-3">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="name" class="form-label">Nom complet</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-dark">Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card soft-card border-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 mb-3">Changer le mot de passe</h2>

                    @if(session('status') === 'password-updated')
                        <div class="alert alert-success">Mot de passe modifie.</div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}" class="d-grid gap-3">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="current_password" class="form-label">Mot de passe actuel</label>
                            <input type="password" id="current_password" name="current_password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" required>
                            @error('current_password', 'updatePassword')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="form-label">Nouveau mot de passe</label>
                            <input type="password" id="password" name="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" required>
                            @error('password', 'updatePassword')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-dark">Mettre a jour</button>
                    </form>
                </div>
            </div>

            <div class="card soft-card border-0">
                <div class="card-body p-4">
                    <h2 class="h4 mb-3 text-danger">Supprimer le compte</h2>
                    <p class="text-muted">Cette action ne peut pas etre annulee.</p>

                    <form method="POST" action="{{ route('profile.destroy') }}" class="d-grid gap-3">
                        @csrf
                        @method('DELETE')

                        <div>
                            <label for="delete_password" class="form-label">Mot de passe</label>
                            <input type="password" id="delete_password" name="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" required>
                            @error('password', 'userDeletion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-outline-danger">Supprimer mon compte</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
