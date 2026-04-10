<?php

namespace App\Mail;

use App\Models\Facture;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FactureClientMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Facture $facture)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre facture '.$this->facture->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.facture-client',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn () => $this->buildPdf(),
                $this->facture->reference.'.pdf'
            )->withMime('application/pdf'),
        ];
    }

    private function buildPdf(): string
    {
        return Pdf::loadView('facture.pdf', [
            'facture' => $this->facture,
        ])->setPaper('a4')->output();
    }
}
