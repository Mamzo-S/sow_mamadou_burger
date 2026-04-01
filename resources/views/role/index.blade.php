@extends('layouts.app')

@section('title', 'Roles')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Liste des roles</h1>
            <p class="text-muted mb-0">Gestion des roles affectes aux utilisateurs.</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoleModal">
            Ajouter un role
        </button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Libelle</th>
                            <th>Utilisateurs</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($roles->isEmpty())
                            <tr>
                                <td colspan="4" class="text-center py-4">Aucun role enregistre.</td>
                            </tr>
                        @else
                            @foreach($roles as $role)
                            <tr>
                                <td>{{ $role->id }}</td>
                                <td>{{ $role->libelle }}</td>
                                <td>{{ $role->users_count }}</td>
                                <td class="text-end">
                                    <a href="{{ route('roles.show', $role->id) }}" class="btn btn-sm btn-outline-secondary">Voir</a>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $role->id }}">
                                        Modifier
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteRoleModal{{ $role->id }}">
                                        Supprimer
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $roles->links('pagination::bootstrap-5') }}
    </div>

    <div class="modal fade" id="createRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form action="{{ route('roles.store') }}" method="POST">
                    @csrf

                    <div class="modal-header">
                        <h2 class="modal-title fs-5">Ajouter un role</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <label for="create_role_libelle" class="form-label">Libelle</label>
                        <input
                            type="text"
                            id="create_role_libelle"
                            name="libelle"
                            class="form-control @error('libelle') is-invalid @enderror"
                            value="{{ old('libelle') }}"
                        >
                        @error('libelle')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach($roles as $role)
        <div class="modal fade" id="editRoleModal{{ $role->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <form action="{{ route('roles.update', $role->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="modal-header">
                            <h2 class="modal-title fs-5">Modifier le role</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <label for="edit_role_libelle_{{ $role->id }}" class="form-label">Libelle</label>
                            <input
                                type="text"
                                id="edit_role_libelle_{{ $role->id }}"
                                name="libelle"
                                class="form-control @error('libelle') is-invalid @enderror"
                                value="{{ $role->libelle }}"
                            >
                            @error('libelle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Mettre a jour</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="deleteRoleModal{{ $role->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5">Confirmer la suppression</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        Voulez-vous vraiment supprimer le role <strong>{{ $role->libelle }}</strong> ?
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <form action="{{ route('roles.destroy', $role->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Supprimer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
