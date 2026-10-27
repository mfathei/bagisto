<?php

namespace Webkul\Saas\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Webkul\Saas\Contracts\BillingRecord;
use Webkul\Saas\Contracts\Tenant;

class PaymentRecordedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Tenant $tenant,
        public BillingRecord $invoice,
        public float $amount
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: (string) config('saas.notifications.from'),
            subject: trans('saas::app.emails.payment-recorded.subject', ['number' => $this->invoice->number])
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(view: 'saas::emails.payment-recorded');
    }
}
