<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Milon\Barcode\DNS2D;
use App\Models\Transaction;

class SuccessPayment extends Mailable
{
    use Queueable, SerializesModels;
    public $transactionId, $custName, $amount, $result;
    /**
     * Create a new message instance.
     */
    public function __construct($transactionId, $custName, $amount, $result)
    {
        $this->transactionId = $transactionId;
        $this->custName = $custName;
        $this->amount = $amount;
        $this->result = $result;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $orderNo = Transaction::select('trx_order_no')->where('transactionId', $this->transactionId)->first();
        return new Envelope(
            subject: 'Payment Successful for [#'.$orderNo['trx_order_no'].']',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.successful-payment',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [ ];
    }
}
