<?php

namespace App\Mail;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;   
use MailerSend\Helpers\Builder\Variable;
use MailerSend\Helpers\Builder\Personalization;

class WEmail extends Mailable
{
    use Queueable, SerializesModels;
    
    /**
     * Create a new message instance.
     */
    public function __construct($query_data)
    {
        $this->query_data = $query_data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'W Email',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.mail',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

}
