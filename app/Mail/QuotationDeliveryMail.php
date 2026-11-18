<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Quotation;
use App\Services\Quotations\QuotationPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class QuotationDeliveryMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Quotation $quotation,
        public readonly string $publicUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Quotation {$this->quotation->quote_number} from ".config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quotations.delivery',
            with: [
                'quotation' => $this->quotation,
                'publicUrl' => $this->publicUrl,
            ],
        );
    }

    /**
     * @return list<Attachment>
     */
    public function attachments(): array
    {
        $pdf = app(QuotationPdfService::class)->render($this->quotation);
        $filename = $this->quotation->quote_number.'.pdf';

        return [
            Attachment::fromData(fn (): string => $pdf, $filename)
                ->withMime('application/pdf'),
        ];
    }
}
