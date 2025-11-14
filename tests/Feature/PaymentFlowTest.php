<?php

namespace Tests\Feature;

use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_process_payment_with_valid_customer_data()
    {
        $customerData = [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'total' => 100000
        ];

        $orderId = 'ORDER123';
        $returnUrl = 'https://example.com/return';

        Livewire::test('public.payments')
            ->set('order_id', $orderId)
            ->set('return_url', $returnUrl)
            ->set('customer_data', $customerData)
            ->call('proceedPayment')
            ->assertRedirect();

        // Verify transaction was created
        $this->assertDatabaseHas('transactions', [
            'trx_order_no' => $orderId,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'amount' => 100000
        ]);

        // Verify session storage instead of cache
        $this->assertTrue(!empty(Session::get('current_transaction_id')));
        $this->assertTrue(!empty(Session::get('transaction_expires_at')));
    }

    /** @test */
    public function it_validates_required_customer_name()
    {
        $customerData = [
            'customer_name' => '',
            'customer_email' => 'john@example.com',
            'total' => 100000
        ];

        Livewire::test('public.payments')
            ->set('order_id', 'ORDER123')
            ->set('customer_data', $customerData)
            ->call('proceedPayment')
            ->assertHasErrors(['customer_name']);
    }

    /** @test */
    public function it_validates_required_customer_email()
    {
        $customerData = [
            'customer_name' => 'John Doe',
            'customer_email' => 'invalid-email',
            'total' => 100000
        ];

        Livewire::test('public.payments')
            ->set('order_id', 'ORDER123')
            ->set('customer_data', $customerData)
            ->call('proceedPayment')
            ->assertHasErrors(['customer_email']);
    }

    /** @test */
    public function it_validates_minimum_amount()
    {
        $customerData = [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'total' => 0
        ];

        Livewire::test('public.payments')
            ->set('order_id', 'ORDER123')
            ->set('customer_data', $customerData)
            ->call('proceedPayment')
            ->assertHasErrors(['amount']);
    }

    /** @test */
    public function it_stores_session_data_per_user()
    {
        $customerData = [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'total' => 200000
        ];

        // Test session isolation
        $component = Livewire::test('public.payments')
            ->set('order_id', 'ORDER456')
            ->set('customer_data', $customerData)
            ->call('proceedPayment');

        $transactionId = Session::get('current_transaction_id');
        $this->assertTrue(!empty($transactionId));
        
        // Verify transaction exists in database
        $this->assertDatabaseHas('transactions', [
            'transactionId' => $transactionId,
            'trx_order_no' => 'ORDER456'
        ]);
    }
}
