<?php

namespace Tests\Unit\Services;

use App\Services\PaymentApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentApiServiceTest extends TestCase
{
    private PaymentApiService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PaymentApiService;

        // Mock the configuration values
        Config::set('services.payment_api.base_url', 'https://tenant.lippomallpuri.com');
        Config::set('services.payment_api.username', 'test@example.com');
        Config::set('services.payment_api.password', 'testpassword');
    }

    public function test_get_token_success(): void
    {
        // Mock the HTTP response for token request
        Http::fake([
            'https://tenant.lippomallpuri.com/revenue/token' => Http::response([
                'token' => 'test_token_value',
                'expires_in' => 3600,
            ], 200),
        ]);

        $result = $this->service->getToken();

        $this->assertTrue($result['success']);
        $this->assertEquals('test_token_value', $result['token']);
        $this->assertArrayHasKey('data', $result);

        // Verify the token was cached
        $this->assertEquals('test_token_value', Cache::get('payment_api_token'));
    }

    public function test_get_token_failure(): void
    {
        // Mock a failed HTTP response for token request
        Http::fake([
            'https://tenant.lippomallpuri.com/revenue/token' => Http::response([
                'message' => 'Invalid credentials',
            ], 400),
        ]);

        $result = $this->service->getToken();

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid credentials', $result['error']);
        $this->assertEquals(400, $result['status_code']);
    }

    public function test_save_revenue_success(): void
    {
        // First, cache a token
        Cache::put('payment_api_token', 'test_token_value', now()->addHour());

        // Mock the HTTP response for save revenue
        Http::fake([
            'https://tenant.lippomallpuri.com/revenue/api/revenue/v1/save' => Http::response([
                'RevenueBatchId' => 123,
                'transactionReturnDatas' => [
                    ['id' => 1, 'transaction_number' => 'TRX001'],
                ],
                'ErrorTransactionNumber' => [],
            ], 200),
        ]);

        $transactionData = [
            [
                'TransactionNumber' => 'TRX001',
                'TransactionDate' => '2023-10-01',
                'Amount' => 100.00,
                'Remarks' => 'Test transaction',
            ],
        ];

        $result = $this->service->saveRevenue($transactionData);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals(123, $result['data']['RevenueBatchId']);
    }

    public function test_get_revenue_success(): void
    {
        // First, cache a token
        Cache::put('payment_api_token', 'test_token_value', now()->addHour());

        // Mock the HTTP response for get revenue
        Http::fake([
            'https://tenant.lippomallpuri.com/revenue/api/revenue/v1/get' => Http::response([
                'RevenueBatchId' => 123,
                'data' => [
                    [
                        'TransactionNumber' => 'TRX001',
                        'TransactionDate' => '2023-10-01',
                        'Amount' => 100.00,
                        'Remarks' => 'Test transaction',
                    ],
                ],
            ], 200),
        ]);

        $result = $this->service->getRevenue(123, 1, 0);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals(123, $result['data']['RevenueBatchId']);
    }

    public function test_get_void_success(): void
    {
        // First, cache a token
        Cache::put('payment_api_token', 'test_token_value', now()->addHour());

        // Mock the HTTP response for get void
        Http::fake([
            'https://tenant.lippomallpuri.com/revenue/api/revenue/v1/getvoid' => Http::response([
                'RevenueBatchId' => 123,
                'data' => [
                    [
                        'TransactionNumber' => 'TRX001',
                        'TransactionDate' => '2023-10-01',
                        'Amount' => -100.00,
                        'Remarks' => 'Void transaction',
                    ],
                ],
            ], 200),
        ]);

        $result = $this->service->getVoid(123);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals(123, $result['data']['RevenueBatchId']);
    }

    public function test_get_summary_success(): void
    {
        // First, cache a token
        Cache::put('payment_api_token', 'test_token_value', now()->addHour());

        // Mock the HTTP response for get summary
        Http::fake([
            'https://tenant.lippomallpuri.com/revenue/api/revenue/v1/getsummary' => Http::response([
                'Months' => 1,
                'Years' => 2023,
                'data' => [
                    [
                        'month' => 1,
                        'year' => 2023,
                        'total_revenue' => 10000.00,
                    ],
                ],
            ], 200),
        ]);

        $result = $this->service->getSummary(1, 2023);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals(1, $result['data']['Months']);
        $this->assertEquals(2023, $result['data']['Years']);
    }
}
