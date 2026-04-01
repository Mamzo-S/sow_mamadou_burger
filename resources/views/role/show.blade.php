@extends('layouts.app')

@section('title', 'Details role')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Role: {{ $role->libelle }}</h1>
        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Retour</a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <p><strong>ID:</strong> {{ $role->id }}</p>
            <p><strong>Libelle:</strong> {{ $role->libelle }}</p>
            <p class="mb-0"><strong>Nombre d'utilisateurs:</strong> {{ $role->users->count() }}</p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h2 class="h5 mb-0">Utilisateurs associes</h2>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nom complet</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($role->users->isEmpty())
                            <tr>
                                <td colspan="2" class="text-center py-4">Aucun utilisateur associe.</td>
                            </tr>
                        @else
                            @foreach($role->users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->prenom }} {{ $user->nom }}</td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
