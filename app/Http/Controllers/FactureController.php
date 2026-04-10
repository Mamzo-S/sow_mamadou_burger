<?php

namespace App\Http\Controllers;

use App\Mail\FactureClientMail;
use App\Models\Commande;
use App\Models\Facture;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FactureController extends Controller
{
    public function index()
    {
        $factures = Facture::with('commande.client')
            ->latest()
            ->paginate(10);
        $commandesDisponibles = Commande::with('client')
            ->whereDoesntHave('facture')
            ->whereIn('statut', ['prete', 'payee'])
            ->orderByDesc('date_commande')
            ->get();
        $toutesCommandes = Commande::with(['client', 'facture'])
            ->where(function ($query) {
                $query->whereIn('statut', ['prete', 'payee'])
                    ->orWhereHas('facture');
            })
            ->orderByDesc('date_commande')
            ->get();
        $stats = [
            'factures_jour' => Facture::whereDate('date_generation', today())->count(),
        ];

        return view('facture.index', compact('factures', 'commandesDisponibles', 'toutesCommandes', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'commande_id' => ['required', 'exists:commandes,id', Rule::unique('factures', 'commande_id')],
            'reference' => ['nullable', 'string', 'max:255', Rule::unique('factures', 'reference')],
            'fichier_pdf' => ['nullable', 'string', 'max:255'],
            'date_generation' => ['required', 'date'],
        ]);

        $validated['reference'] = $validated['reference'] ?: $this->generateFactureReference();
        $validated['date_generation'] = Carbon::parse($validated['date_generation']);

        $facture = Facture::create($validated);

        $this->sendFactureToClient($facture);

        return to_route('factures.index')->with('success', 'Facture creee.');
    }

    public function show(Request $request, string $id)
    {
        $facture = Facture::with(['commande.client', 'commande.ligneCommandes.burger', 'commande.paiement'])->findOrFail($id);
        $this->authorizeAccess($request, $facture);

        return view('facture.show', compact('facture'));
    }

    public function update(Request $request, string $id)
    {
        $facture = Facture::findOrFail($id);

        $validated = $request->validate([
            'commande_id' => ['required', 'exists:commandes,id', Rule::unique('factures', 'commande_id')->ignore($facture->id)],
            'reference' => ['required', 'string', 'max:255', Rule::unique('factures', 'reference')->ignore($facture->id)],
            'fichier_pdf' => ['nullable', 'string', 'max:255'],
            'date_generation' => ['required', 'date'],
        ]);

        $validated['date_generation'] = Carbon::parse($validated['date_generation']);

        $facture->update($validated);

        return to_route('factures.index')->with('success', 'Facture modifiee.');
    }

    public function destroy(string $id)
    {
        $facture = Facture::findOrFail($id);

        if ($facture->fichier_pdf && Storage::disk('public')->exists($facture->fichier_pdf)) {
            Storage::disk('public')->delete($facture->fichier_pdf);
        }

        $facture->delete();

        return to_route('factures.index')->with('delete', 'Facture supprimee.');
    }

    public function downloadPdf(Request $request, string $id)
    {
        $facture = Facture::with(['commande.client', 'commande.ligneCommandes.burger', 'commande.paiement'])->findOrFail($id);
        $this->authorizeAccess($request, $facture);
        $relativePath = 'factures/'.$facture->reference.'.pdf';

        $pdf = Pdf::loadView('facture.pdf', compact('facture'))
            ->setPaper('a4');
        $output = $pdf->output();

        Storage::disk('public')->put($relativePath, $output);

        if ($facture->fichier_pdf !== $relativePath) {
            $facture->update(['fichier_pdf' => $relativePath]);
        }

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$facture->reference.'.pdf"',
        ]);
    }

    private function generateFactureReference(): string
    {
        do {
            $reference = 'FAC-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        } while (Facture::where('reference', $reference)->exists());

        return $reference;
    }

    private function authorizeAccess(Request $request, Facture $facture): void
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if ($user->isAdminOrManager()) {
            return;
        }

        if ($user->isClient() && $facture->commande?->client_id === $user->id) {
            return;
        }

        abort(403);
    }

    private function sendFactureToClient(Facture $facture): void
    {
        $facture->loadMissing(['commande.client', 'commande.ligneCommandes.burger', 'commande.paiement']);

        $email = $facture->commande?->client?->email;

        if (! $email) {
            return;
        }

        Mail::to($email)->send(new FactureClientMail($facture));
    }
}
