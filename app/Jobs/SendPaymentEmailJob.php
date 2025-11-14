<?php

namespace App\Jobs;

use App\Mail\PendingPayment;
use App\Mail\SuccessPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendPaymentEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $email,
        private string $type,
        private string $transactionId,
        private string $customerName,
        private float $amount,
        private array $paymentData
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            match ($this->type) {
                'pending' => Mail::mailer('payment_pending')
                    ->to($this->email)
                    ->send(new PendingPayment(
                        $this->transactionId,
                        $this->customerName,
                        $this->amount,
                        $this->paymentData
                    )),
                'success' => Mail::mailer('payment_success')
                    ->to($this->email)
                    ->send(new SuccessPayment(
                        $this->transactionId,
                        $this->customerName,
                        $this->amount,
                        $this->paymentData
                    )),
                default => throw new \InvalidArgumentException("Invalid email type: {$this->type}")
            };

            Log::info('Payment email sent successfully', [
                'type' => $this->type,
                'email' => $this->email,
                'transaction_id' => $this->transactionId
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment email', [
                'error' => $e->getMessage(),
                'type' => $this->type,
                'email' => $this->email,
                'transaction_id' => $this->transactionId
            ]);
            throw $e;
        }
    }

    /**
     * The job failed to process.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Payment email job failed', [
            'error' => $exception->getMessage(),
            'type' => $this->type,
            'email' => $this->email,
            'transaction_id' => $this->transactionId
        ]);
    }
}