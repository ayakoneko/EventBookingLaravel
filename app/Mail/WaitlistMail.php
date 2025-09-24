<?php

namespace App\Mail;

use App\Models\Waitlist;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;


/**
 * Notification email sent to a user on a waitlist when a spot becomes available.
 * Encapsulates subject, body content, and view data for rendering the waitlist offer.
 */

class WaitlistMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     * The waitlist entry driving this notification.
     */
    public function __construct(public Waitlist $waitlist)
    {
        //
    }

    /**
     * Get the message envelope.
     * 
     * Subject line includes the event title so recipients can quickly
     * identify which waitlist offer this relates to.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'A spot just opened for '.$this->waitlist->event->title,
        );
    }

    /**
     * Get the message content definition.
     * 
     * Supplies the event, waitlist, and a direct link to the event details
     * so recipients can respond promptly to the offer.
     */
    public function content(): Content
    {
        $event = $this->waitlist->event;
        
        return new Content(
            view: 'emails.waitlist',
            with: [
                'event'    => $event,
                'waitlist' => $this->waitlist,
                'url'      => route('events.show', $event),
            ],
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
