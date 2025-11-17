<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PendingPayment extends Mailable
{
    use Queueable, SerializesModels;

    public $transactionId;

    public $custName;

    public $amount;

    public $result;

    /**
     * Create a new message instance.
     */
    public function __construct($transactionId, $custName, $amount, $result)
    {
        $this->transactionId = $transactionId;
        $this->custName = $custName;
        $this->amount = $amount;
        $this->result = $result;
        $this->url = route('query_payment_email', ['trxId' => base64_encode($transactionId)]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $orderNo = Transaction::select('trx_order_no')->where('transactionId', $this->transactionId)->first();

        return new Envelope(
            subject: 'Pending Payment for [#'.$orderNo['trx_order_no'].']',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.pending-payment',
            with: [
                'url' => $this->url,
            ]
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
