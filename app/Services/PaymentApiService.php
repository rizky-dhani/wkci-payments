<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class PaymentApiService
{
    protected string $baseUrl;
    protected string $cacheKey = 'payment_api_token';

    public function __construct()
    {
        $this->baseUrl = config('services.payment_api.base_url');
    }

    /**
     * Get authentication token from the API
     * POST - Token
     * https://tenant.lippomallpuri.com/revenue/token
     */
    public function getToken(): array
    {
        $response = Http::asForm()->post($this->baseUrl . '/revenue/token', [
            'grant_type' => 'password',
            'username' => config('services.payment_api.username'),
            'password' => config('services.payment_api.password'),
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $token = $data['token'] ?? null;

            if ($token) {
                // Cache token with a short expiration time (e.g., 55 minutes if token expires in 60 min)
                $expiresIn = ($data['expires_in'] ?? 3300) - 60; // Refresh 1 minute before expiration
                Cache::put($this->cacheKey, $token, now()->addSeconds($expiresIn));
                
                return [
                    'success' => true,
                    'token' => $token,
                    'data' => $data
                ];
            }
        }

        return [
            'success' => false,
            'error' => $response->json()['message'] ?? 'Failed to retrieve token',
            'status_code' => $response->status(),
            'response_data' => $response->json()
        ];
    }

    /**
     * Get the current token from cache or fetch a new one
     */
    protected function getValidToken(): ?string
    {
        $token = Cache::get($this->cacheKey);

        if (!$token) {
            $result = $this->getToken();
            if ($result['success']) {
                $token = $result['token'];
            } else {
                throw new \Exception('Unable to retrieve API token: ' . $result['error']);
            }
        }

        return $token;
    }

    /**
     * Make an authenticated API request
     */
    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $token = $this->getValidToken();

        $request = Http::withToken($token)->acceptJson();

        $response = $request->$method($this->baseUrl . $endpoint, $data);

        if ($response->status() === 401) { // Token might be expired
            Cache::forget($this->cacheKey); // Clear expired token
            $token = $this->getValidToken();
            
            if (!$token) {
                return [
                    'success' => false,
                    'error' => 'Authentication required',
                    'status_code' => 401
                ];
            }
            
            $request = Http::withToken($token)->acceptJson();
            $response = $request->$method($this->baseUrl . $endpoint, $data);
        }

        if ($response->successful()) {
            return [
                'success' => true,
                'data' => $response->json(),
                'status_code' => $response->status()
            ];
        }

        return [
            'success' => false,
            'error' => $response->json()['message'] ?? 'API request failed',
            'status_code' => $response->status(),
            'response_data' => $response->json()
        ];
    }

    /**
     * Save revenue data to the API
     * POST - Save Revenue
     * https://tenant.lippomallpuri.com/revenue/api/revenue/v1/save
     */
    public function saveRevenue(array $transactionDatas): array
    {
        $payload = [
            'TransactionDatas' => $transactionDatas
        ];

        return $this->makeRequest('post', '/revenue/api/revenue/v1/save', $payload);
    }

    /**
     * Get revenue data from the API
     * POST - Get Revenue
     * https://tenant.lippomallpuri.com/revenue/api/revenue/v1/get
     */
    public function getRevenue(int $revenueBatchId, int $page = 1, int $number = 0): array
    {
        $payload = [
            'RevenueBatchId' => $revenueBatchId,
            'Page' => $page,
            'Number' => $number
        ];

        return $this->makeRequest('post', '/revenue/api/revenue/v1/get', $payload);
    }

    /**
     * Get void data from the API
     * POST - Get Void
     * https://tenant.lippomallpuri.com/revenue/api/revenue/v1/getvoid
     */
    public function getVoid(int $revenueBatchId): array
    {
        $payload = [
            'RevenueBatchId' => $revenueBatchId
        ];

        return $this->makeRequest('post', '/revenue/api/revenue/v1/getvoid', $payload);
    }

    /**
     * Get summary data from the API
     * POST - Get Summary
     * https://tenant.lippomallpuri.com/revenue/api/revenue/v1/getsummary
     */
    public function getSummary(int $months, int $years): array
    {
        $payload = [
            'Months' => $months,
            'Years' => $years
        ];

        return $this->makeRequest('post', '/revenue/api/revenue/v1/getsummary', $payload);
    }
}