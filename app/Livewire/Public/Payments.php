<?php

namespace App\Livewire\Public;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Payments extends Component
{
    public $order_id;

    public $return_url;

    public $customer_data;

    #[Validate('required|string')]
    public $validated_order_id;

    #[Validate('required|array')]
    public $validated_customer_data;

    #[Validate('required|string|min:1', message: 'Customer name is required')]
    public $customer_name;

    #[Validate('required|email', message: 'Valid email is required')]
    public $customer_email;

    #[Validate('required|numeric|min:1', message: 'Amount must be greater than 0')]
    public $amount;

    public function mount()
    {
        try {
            // Get order id from wkci main website
            $this->order_id = base64_decode(request()->query('order_id'));
            $this->return_url = base64_decode(request()->query('return_url'));

            // Get customer data from wkci main website and decode it
            $customer_data = request()->query('customer_data');
            $this->customer_data = json_decode(base64_decode($customer_data), true);

            // Validate and set individual properties for validation
            if ($this->customer_data) {
                $this->validated_order_id = $this->order_id;
                $this->validated_customer_data = $this->customer_data;
                $this->customer_name = $this->customer_data['customer_name'] ?? '';
                $this->customer_email = $this->customer_data['customer_email'] ?? '';
                $this->amount = $this->customer_data['total'] ?? 0;
            }

        } catch (\Exception $e) {
            Log::error('Failed to decode payment data', [
                'error' => $e->getMessage(),
                'query_params' => request()->query(),
            ]);

            session()->flash('error', 'Invalid payment data. Please try again from the main website.');
        }
    }

    public function proceedPayment()
    {
        try {
            // Validate the form data
            $this->validate();

            // Additional validation for customer data structure
            if (empty($this->customer_data['customer_name']) ||
                empty($this->customer_data['customer_email']) ||
                empty($this->customer_data['total'])) {
                throw new \Exception('Missing required customer information.');
            }

            $data = [
                'order_id' => $this->order_id,
                'return_url' => $this->return_url,
                'customer_data' => $this->customer_data,
            ];

            // Use session-based storage instead of global cache
            $sessionKey = 'orderData_'.session()->getId();
            Session::put($sessionKey, $data);
            Session::put('orderData_expires_at', now()->addMinutes(30));

            $uuid = (string) Str::orderedUuid();

            $transaction = Transaction::create([
                'transactionId' => $uuid,
                'trx_order_no' => $this->order_id,
                'customer_name' => $this->customer_data['customer_name'],
                'customer_email' => $this->customer_data['customer_email'],
                'amount' => $this->customer_data['total'],
                'submitted_date' => Carbon::now()->setTimezone('Asia/Jakarta'),
            ]);

            // Store transaction ID in session instead of global cache
            Session::put('current_transaction_id', $uuid);
            Session::put('transaction_expires_at', now()->addHours(2));

            Log::info('Payment initiated', [
                'transaction_id' => $uuid,
                'order_id' => $this->order_id,
                'customer_email' => $this->customer_data['customer_email'],
                'amount' => $this->customer_data['total'],
            ]);

            return $this->redirectRoute('generate_qr', [
                'trx_id' => $uuid,
                'order_id' => $transaction['trx_order_no'],
                'customer_name' => $transaction['customer_name'],
                'customer_email' => $transaction['customer_email'],
                'amount' => $transaction['amount'],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Let Livewire handle validation errors
            throw $e;
        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'error' => $e->getMessage(),
                'order_id' => $this->order_id ?? 'unknown',
                'customer_data' => $this->customer_data ?? [],
            ]);

            session()->flash('error', 'Payment processing failed. Please try again.');

            return;
        }
    }

    /**
     * Get order data from session for current user
     */
    public static function getOrderDataFromSession()
    {
        $sessionKey = 'orderData_'.session()->getId();
        $expiresAt = Session::get('orderData_expires_at');

        if ($expiresAt && now()->greaterThan($expiresAt)) {
            Session::forget([$sessionKey, 'orderData_expires_at']);

            return null;
        }

        return Session::get($sessionKey);
    }

    /**
     * Get current transaction ID from session
     */
    public static function getCurrentTransactionId()
    {
        $expiresAt = Session::get('transaction_expires_at');

        if ($expiresAt && now()->greaterThan($expiresAt)) {
            Session::forget(['current_transaction_id', 'transaction_expires_at']);

            return null;
        }

        return Session::get('current_transaction_id');
    }

    #[Title('Payments')]
    #[Layout('components.layouts.public')]
    public function render()
    {
        return view('livewire.public.payments', [
            'order_id' => $this->order_id,
            'data' => $this->customer_data,
        ]);
    }
}
