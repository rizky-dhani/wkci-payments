<?php

namespace App\Services;

use Automattic\WooCommerce\Client;
use Illuminate\Support\Facades\Log;

class WooCommerceService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client(
            config('woocommerce.store_url'),
            config('woocommerce.consumer_key'),
            config('woocommerce.consumer_secret'),
            [
                'wp_api' => true,
                'version' => 'wc/v3',
                'verify_ssl' => false,
            ]
        );
    }

    /**
     * Update order status to completed
     */
    public function completeOrder(string $orderId): bool
    {
        try {
            $this->client->put('orders/'.$orderId, [
                'status' => 'completed',
            ]);

            Log::info('WooCommerce order completed', ['order_id' => $orderId]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to complete WooCommerce order', [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
            ]);

            return false;
        }
    }

    /**
     * Get order details
     */
    public function getOrder(string $orderId): ?array
    {
        try {
            $response = $this->client->get('orders/'.$orderId);

            return (array) $response;
        } catch (\Exception $e) {
            Log::error('Failed to get WooCommerce order', [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
            ]);

            return null;
        }
    }
}
