<?php

namespace Webkul\Saas\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Webkul\Saas\Contracts\ContactInquiry;

class ContactInquiryMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public ContactInquiry $inquiry) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = preg_replace('/[\r\n]+/', ' ', (string) $this->inquiry->subject) ?? '';

        return new Envelope(
            from: (string) config('saas.notifications.from'),
            subject: trans('saas::marketing.contact.mail_subject', [
                'subject' => mb_substr($subject, 0, 80),
            ])
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(view: 'saas::emails.contact-inquiry');
    }
}
