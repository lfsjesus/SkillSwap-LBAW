<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;


class MailModel extends Mailable
{
    public $mailData;

    /**
     * Create a new message instance.
     */
    public function __construct($mailData) {
        $this->mailData = $mailData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope() {
        return new Envelope(
            from: new Address(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME')),
            subject: 'LBAW Tutorial 01 - Send Email',
        );
    }
    
    /**
     * Get the message content definition.
     */
    public function content() {
        return new Content(
            view: 'emails.example',
        );
    }
}
