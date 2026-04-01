@extends('layouts.app')

@section('title', 'Confirmation du mot de passe')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card soft-card bg-white p-4 border-0">
                <h1 class="h3 mb-3">Confirmation du mot de passe</h1>
                <p class="text-muted mb-4">Pour continuer, entrez a nouveau votre mot de passe.</p>

                <form method="POST" action="{{ route('password.confirm') }}" class="d-grid gap-3">
                    @csrf
                    <div>
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-dark">Confirmer</button>
                </form>
            </div>
        </div>
    </div>
@endsection
