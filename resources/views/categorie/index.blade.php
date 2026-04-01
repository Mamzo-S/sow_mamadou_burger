@extends('layouts.app')

@section('title', 'Gestion categories')

@section('content')
    <section class="hero-panel p-4 p-md-5 soft-card mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h1 class="display-6 fw-semibold mb-0">Gestion des categories</h1>
            </div>
            <div class="col-lg-4 text-lg-end">
                <button type="button" class="btn btn-warning btn-lg" data-bs-toggle="modal" data-bs-target="#createCategorieModal">
                    <i class="bi bi-plus-lg me-1"></i> Ajouter une categorie
                </button>
            </div>
        </div>
    </section>

    <section class="row g-4">
        @if($categories->isEmpty())
            <div class="col-12">
                <div class="soft-card bg-white p-5 text-center">
                    <h2 class="h4 mb-0">Aucune categorie enregistree</h2>
                </div>
            </div>
        @else
            @foreach($categories as $categorie)
                <div class="col-md-6 col-xl-4">
                    <div class="card soft-card hover-lift h-100">
                        <div class="card-body p-3 p-md-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <div>
                                    <div class="text-muted small">Categorie</div>
                                    <h2 class="h4 mb-1">{{ $categorie->nom }}</h2>
                                </div>
                                <span class="badge text-bg-dark">{{ $categorie->burgers_count }} burgers</span>
                            </div>

                            @if($categorie->description)
                                <div class="text-muted">{{ $categorie->description }}</div>
                            @endif

                            <div class="mb-4">
                                <div class="small text-muted mb-2">Apercu du contenu</div>
                                @if($categorie->burgers->isEmpty())
                                    <span class="badge text-bg-light border">Aucun burger</span>
                                @else
                                    @foreach($categorie->burgers->take(3) as $burger)
                                        <span class="badge text-bg-light border me-1 mb-1">{{ $burger->nom }}</span>
                                    @endforeach
                                @endif
                            </div>

                            <div class="d-flex gap-2 mt-auto">
                                <a href="{{ route('categories.show', $categorie->id) }}" class="btn btn-outline-dark flex-fill">Voir</a>
                                <button type="button" class="btn btn-outline-primary flex-fill" data-bs-toggle="modal" data-bs-target="#editCategorieModal{{ $categorie->id }}">
                                    Modifier
                                </button>
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteCategorieModal{{ $categorie->id }}">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </section>

    <div class="mt-4">
        {{ $categories->links('pagination::bootstrap-5') }}
    </div>

    <div class="modal fade" id="createCategorieModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form action="{{ route('categories.store') }}" method="POST">
                    @csrf

                    <div class="modal-header">
                        <h2 class="modal-title fs-5">Ajouter une categorie</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="create_categorie_nom" class="form-label">Nom</label>
                            <input type="text" id="create_categorie_nom" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom') }}">
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-0">
                            <label for="create_categorie_description" class="form-label">Description</label>
                            <textarea id="create_categorie_description" name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach($categories as $categorie)
        <div class="modal fade" id="editCategorieModal{{ $categorie->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <form action="{{ route('categories.update', $categorie->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="modal-header">
                            <h2 class="modal-title fs-5">Modifier la categorie</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="edit_categorie_nom_{{ $categorie->id }}" class="form-label">Nom</label>
                                <input type="text" id="edit_categorie_nom_{{ $categorie->id }}" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ $categorie->nom }}">
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label for="edit_categorie_description_{{ $categorie->id }}" class="form-label">Description</label>
                                <textarea id="edit_categorie_description_{{ $categorie->id }}" name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ $categorie->description }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Mettre a jour</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="deleteCategorieModal{{ $categorie->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5">Supprimer la categorie</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        Voulez-vous vraiment supprimer <strong>{{ $categorie->nom }}</strong> ?
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <form action="{{ route('categories.destroy', $categorie->id) }}" method="POST">
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
