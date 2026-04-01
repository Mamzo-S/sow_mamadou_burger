@extends('layouts.app')

@section('title', 'Detail du burger')

@section('content')
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card soft-card border-0 h-100">
                <div class="card-body p-4">
                    @php
                        $imageUrl = $burger->image ? \Illuminate\Support\Facades\Storage::url($burger->image) : null;
                    @endphp
                    <div class="burger-thumb mb-4">
                        @if($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $burger->nom }}">
                        @endif
                        <div class="burger-thumb-label">
                            <div class="small text-uppercase opacity-75">{{ $burger->categorie?->nom ?: 'Sans categorie' }}</div>
                            <div class="display-6">{{ $burger->nom }}</div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge text-bg-dark">{{ $burger->disponible ? 'Disponible' : 'Indisponible' }}</span>
                        <span class="badge text-bg-light border">Stock: {{ $burger->stock }}</span>
                    </div>

                    <div class="fs-4 fw-semibold mb-3">{{ number_format((float) $burger->prix, 2, ',', ' ') }} FCFA</div>

                    @if($burger->description)
                        <div class="text-muted">{{ $burger->description }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card soft-card border-0">
                <div class="card-body p-4">
                    <h1 class="h3 mb-4">{{ $burger->nom }}</h1>
                    <div class="mb-2"><strong>Categorie :</strong> {{ $burger->categorie?->nom ?: 'Aucune' }}</div>
                    <div class="mb-4"><strong>Image :</strong> {{ $burger->image ? 'Disponible' : 'Aucune' }}</div>

                    @if(auth()->user()?->isClient())
                        <form action="{{ route('client.orders.store') }}" method="POST" class="d-grid gap-3">
                            @csrf
                            <input type="hidden" name="burger_id" value="{{ $burger->id }}">

                            <div>
                                <label for="quantite" class="form-label">Quantite</label>
                                <input
                                    type="number"
                                    id="quantite"
                                    name="quantite"
                                    min="1"
                                    max="{{ $burger->stock }}"
                                    value="1"
                                    class="form-control"
                                    {{ ! $burger->disponible || $burger->stock < 1 ? 'disabled' : '' }}
                                >
                            </div>

                            <button type="submit" class="btn btn-warning {{ ! $burger->disponible || $burger->stock < 1 ? 'disabled' : '' }}">
                                Commander
                            </button>
                        </form>
                    @else
                        <a href="{{ route('commandes.create', ['burger' => $burger->id]) }}" class="btn btn-warning w-100 mb-2 {{ ! $burger->disponible || $burger->stock < 1 ? 'disabled' : '' }}">
                            Commander
                        </a>
                    @endif

                    <a href="{{ route($returnRoute) }}" class="btn btn-outline-secondary w-100 mt-2">Retour</a>

                    @if($canManage)
                        <a href="{{ route('burgers.index') }}" class="btn btn-outline-dark w-100 mt-2">Gestion des burgers</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
