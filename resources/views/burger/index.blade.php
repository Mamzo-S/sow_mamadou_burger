@extends('layouts.app')

@section('title', 'Gestion burgers')

@section('content')
    <section class="hero-panel p-4 p-md-5 soft-card mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h1 class="display-6 fw-semibold mb-0">Gestion des burgers</h1>
            </div>
            <div class="col-lg-4 text-lg-end">
                <button type="button" class="btn btn-warning btn-lg" data-bs-toggle="modal" data-bs-target="#createBurgerModal">
                    <i class="bi bi-plus-lg me-1"></i> Ajouter un burger
                </button>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="soft-card bg-white p-4 h-100">
                    <div class="text-muted small">Burgers affiches</div>
                    <div class="fs-2 fw-semibold">{{ $burgers->total() }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="soft-card bg-white p-4 h-100">
                    <div class="text-muted small">Categories disponibles</div>
                    <div class="fs-2 fw-semibold">{{ $categories->count() }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="soft-card bg-white p-4 h-100">
                    <div class="text-muted small">Catalogue public</div>
                    <a href="{{ route('site.client') }}" class="btn btn-outline-dark mt-2">Voir la page client</a>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-4">
        @if($burgers->isEmpty())
            <div class="col-12">
                <div class="soft-card bg-white p-5 text-center">
                    <h2 class="h4 mb-0">Aucun burger enregistre</h2>
                </div>
            </div>
        @else
            @foreach($burgers as $burger)
                <div class="col-md-6 col-xl-4">
                    <div class="card soft-card hover-lift h-100 {{ $burger->est_archive ? 'opacity-75' : '' }}">
                        <div class="card-body p-3 p-md-4 d-flex flex-column">
                            @php
                                $imageUrl = $burger->image ? \Illuminate\Support\Facades\Storage::url($burger->image) : null;
                            @endphp
                            <div class="burger-thumb mb-3">
                                @if($imageUrl)
                                    <img src="{{ $imageUrl }}" alt="{{ $burger->nom }}">
                                @endif
                                <div class="burger-thumb-label">
                                    <div class="small text-uppercase opacity-75">{{ $burger->categorie?->nom ?: 'Sans categorie' }}</div>
                                    <div class="fs-4">{{ $burger->nom }}</div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge text-bg-dark">{{ $burger->disponible ? 'Disponible' : 'Indisponible' }}</span>
                                <span class="badge text-bg-light border">Stock: {{ $burger->stock }}</span>
                                @if($burger->est_archive)
                                    <span class="badge text-bg-warning">Archive</span>
                                @endif
                            </div>

                            <div class="mb-3">
                                <div class="text-muted small">Prix</div>
                                <div class="fs-5 fw-semibold">{{ number_format((float) $burger->prix, 2, ',', ' ') }} FCFA</div>
                            </div>

                            @if($burger->description)
                                <div class="text-muted flex-grow-1">{{ $burger->description }}</div>
                            @else
                                <div class="flex-grow-1"></div>
                            @endif

                            <div class="d-flex gap-2 pt-2">
                                <a href="{{ route('burgers.show', $burger->id) }}" class="btn btn-outline-dark flex-fill">Voir</a>
                                <button type="button" class="btn btn-outline-primary flex-fill" data-bs-toggle="modal" data-bs-target="#editBurgerModal{{ $burger->id }}">
                                    Modifier
                                </button>
                                <form action="{{ route('burgers.archive', $burger->id) }}" method="POST" onsubmit="return confirm('{{ $burger->est_archive ? 'Desarchiver' : 'Archiver' }} ce burger ?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn {{ $burger->est_archive ? 'btn-outline-success' : 'btn-outline-warning' }}">
                                        <i class="bi {{ $burger->est_archive ? 'bi-arrow-counterclockwise' : 'bi-archive' }}"></i>
                                    </button>
                                </form>
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteBurgerModal{{ $burger->id }}">
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
        {{ $burgers->links('pagination::bootstrap-5') }}
    </div>

    <div class="modal fade" id="createBurgerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <form action="{{ route('burgers.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-header">
                        <h2 class="modal-title fs-5">Ajouter un burger</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="create_burger_nom" class="form-label">Nom</label>
                            <input type="text" id="create_burger_nom" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom') }}">
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="create_burger_categorie" class="form-label">Categorie</label>
                            <select id="create_burger_categorie" name="categorie_id" class="form-select @error('categorie_id') is-invalid @enderror">
                                <option value="">Choisir une categorie</option>
                                @foreach($categories as $categorie)
                                    <option value="{{ $categorie->id }}" @selected(old('categorie_id') == $categorie->id)>{{ $categorie->nom }}</option>
                                @endforeach
                            </select>
                            @error('categorie_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="create_burger_description" class="form-label">Description</label>
                            <textarea id="create_burger_description" name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="create_burger_prix" class="form-label">Prix</label>
                                <input type="number" step="0.01" min="0" id="create_burger_prix" name="prix" class="form-control @error('prix') is-invalid @enderror" value="{{ old('prix') }}">
                                @error('prix')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="create_burger_stock" class="form-label">Stock</label>
                                <input type="number" min="0" id="create_burger_stock" name="stock" class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', 0) }}">
                                @error('stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="create_burger_image" class="form-label">Image</label>
                            <input type="file" id="create_burger_image" name="image" class="form-control @error('image') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp,image/*">
                            <div class="form-text">Formats acceptes: jpg, jpeg, png, webp. Taille max: 2 Mo.</div>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check">
                            <input type="checkbox" id="create_burger_disponible" name="disponible" value="1" class="form-check-input" @checked(old('disponible', 1))>
                            <label for="create_burger_disponible" class="form-check-label">Disponible</label>
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

    @foreach($burgers as $burger)
        <div class="modal fade" id="editBurgerModal{{ $burger->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <form action="{{ route('burgers.update', $burger->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="modal-header">
                            <h2 class="modal-title fs-5">Modifier le burger</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="edit_burger_nom_{{ $burger->id }}" class="form-label">Nom</label>
                                <input type="text" id="edit_burger_nom_{{ $burger->id }}" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ $burger->nom }}">
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="edit_burger_categorie_{{ $burger->id }}" class="form-label">Categorie</label>
                                <select id="edit_burger_categorie_{{ $burger->id }}" name="categorie_id" class="form-select @error('categorie_id') is-invalid @enderror">
                                    <option value="">Choisir une categorie</option>
                                    @foreach($categories as $categorie)
                                        <option value="{{ $categorie->id }}" @selected($burger->categorie_id == $categorie->id)>{{ $categorie->nom }}</option>
                                    @endforeach
                                </select>
                                @error('categorie_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="edit_burger_description_{{ $burger->id }}" class="form-label">Description</label>
                                <textarea id="edit_burger_description_{{ $burger->id }}" name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ $burger->description }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_burger_prix_{{ $burger->id }}" class="form-label">Prix</label>
                                    <input type="number" step="0.01" min="0" id="edit_burger_prix_{{ $burger->id }}" name="prix" class="form-control @error('prix') is-invalid @enderror" value="{{ $burger->prix }}">
                                    @error('prix')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_burger_stock_{{ $burger->id }}" class="form-label">Stock</label>
                                    <input type="number" min="0" id="edit_burger_stock_{{ $burger->id }}" name="stock" class="form-control @error('stock') is-invalid @enderror" value="{{ $burger->stock }}">
                                    @error('stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="edit_burger_image_{{ $burger->id }}" class="form-label">Image</label>
                                <input type="file" id="edit_burger_image_{{ $burger->id }}" name="image" class="form-control @error('image') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp,image/*">
                                @if($burger->image)
                                    <div class="mt-2">
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($burger->image) }}" alt="{{ $burger->nom }}" class="img-fluid rounded-3 border" style="max-height: 120px;">
                                    </div>
                                @endif
                                <div class="form-text">Laisse vide si tu veux garder l'image actuelle.</div>
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-check">
                                <input type="checkbox" id="edit_burger_disponible_{{ $burger->id }}" name="disponible" value="1" class="form-check-input" @checked($burger->disponible)>
                                <label for="edit_burger_disponible_{{ $burger->id }}" class="form-check-label">Disponible</label>
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

        <div class="modal fade" id="deleteBurgerModal{{ $burger->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5">Supprimer le burger</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Voulez-vous vraiment supprimer <strong>{{ $burger->nom }}</strong> ?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <form action="{{ route('burgers.destroy', $burger->id) }}" method="POST">
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
