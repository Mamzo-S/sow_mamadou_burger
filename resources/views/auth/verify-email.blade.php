@extends('layouts.app')

@section('title', 'Verification email')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card soft-card bg-white p-4 border-0">
                <h1 class="h3 mb-3">Verifier votre email</h1>
                <p class="text-muted mb-4">Consultez votre boite mail et cliquez sur le lien recu. Si besoin, vous pouvez demander un nouvel envoi.</p>

                @if (session('status') === 'verification-link-sent')
                    <div class="alert alert-success">Un nouveau lien de verification a ete envoye.</div>
                @endif

                <div class="d-flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-dark">Renvoyer le lien</button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">Se deconnecter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
