<?php

namespace App\Services;

use App\Models\ApiRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentApiService
{
    protected string $baseUrl;

    protected string $cacheKey = 'lippo_api_token';

    public function __construct()
    {
        $this->baseUrl = config('services.lippo_api.base_url');
    }

    /**
     * Get authentication token from the API
     * POST - Token
     * https://tenant.lippomallpuri.com/revenue/token
     */
    public function getToken(): array
    {
        $username = config('services.lippo_api.username');
        $password = config('services.lippo_api.password');
        $baseUrl = config('services.lippo_api.base_url');

        Log::info('Attempting to retrieve token', [
            'base_url' => $this->baseUrl,
            'username' => $username ? '***' : 'NULL', // Don't log actual username for security
            'password' => $password ? '***' : 'NULL',  // Don't log actual password for security
        ]);

        if (! $username || ! $password || ! $baseUrl) {
            $errorMsg = 'Missing configuration for Lippo API: '.
                       (! $baseUrl ? 'base_url, ' : '').
                       (! $username ? 'username, ' : '').
                       (! $password ? 'password, ' : '');

            Log::error('Missing Lippo API configuration', [
                'base_url' => $baseUrl,
                'username' => $username !== null,
                'password' => $password !== null,
            ]);

            return [
                'success' => false,
                'error' => trim($errorMsg, ', '),
                'status_code' => 500,
            ];
        }

        $requestData = [
            'grant_type' => 'password',
            'username' => $username,
            'password' => $password,
        ];

        $requestBody = $requestData;
        $requestHeaders = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ];

        $startTime = microtime(true);
        $response = Http::withHeaders($requestHeaders)->asForm()->post($this->baseUrl.'/revenue/token', $requestData);
        $executionTime = microtime(true) - $startTime;

        // Log the API request
        ApiRequest::create([
            'endpoint' => $this->baseUrl.'/revenue/token',
            'method' => 'POST',
            'request_headers' => $requestHeaders,
            'request_body' => $requestBody,
            'response_status' => $response->status(),
            'response_headers' => $response->headers(),
            'response_body' => $response->json(),
            'execution_time' => $executionTime,
            'error_message' => $response->successful() ? null : $response->body(),
        ]);

        Log::info('Token request sent', [
            'url' => $this->baseUrl.'/revenue/token',
            'status' => $response->status(),
            'execution_time' => $executionTime,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            // The API returns 'access_token' instead of 'token'
            $token = $data['access_token'] ?? $data['token'] ?? null;

            Log::info('Token response received', [
                'has_token' => $token !== null,
                'response_keys' => array_keys($data ?? []),
            ]);

            if ($token) {
                // Cache token with a short expiration time (e.g., 55 minutes if token expires in 60 min)
                $expiresIn = ($data['expires_in'] ?? 3300) - 60; // Refresh 1 minute before expiration
                Cache::put($this->cacheKey, $token, now()->addSeconds($expiresIn));

                Log::info('Token retrieved and cached successfully');

                return [
                    'success' => true,
                    'token' => $token,
                    'data' => $data,
                ];
            } else {
                Log::error('Token not found in response', [
                    'response' => $data,
                ]);
            }
        } else {
            Log::error('Token request failed', [
                'status' => $response->status(),
                'response_body' => $response->body(),
            ]);
        }

        $error_message = $response->json()['message'] ?? 'Failed to retrieve token';

        Log::error('Failed to retrieve token', [
            'status' => $response->status(),
            'response' => $response->json(),
            'error_message' => $error_message,
        ]);

        return [
            'success' => false,
            'error' => $error_message,
            'status_code' => $response->status(),
            'response_data' => $response->json(),
        ];
    }

    /**
     * Get the current token from cache or fetch a new one
     */
    protected function getValidToken(): ?string
    {
        $token = Cache::get($this->cacheKey);

        if (! $token) {
            $result = $this->getToken();
            if ($result['success']) {
                $token = $result['token'];
            } else {
                Log::error('Unable to retrieve API token', ['error' => $result['error']]);
                throw new \Exception('Unable to retrieve API token: '.$result['error']);
            }
        }

        return $token;
    }

    /**
     * Make an authenticated API request
     */
    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        try {
            $token = $this->getValidToken();

            // Set appropriate content type based on endpoint
            $requestHeaders = [
                'Authorization' => 'Bearer '.$token,
                'Accept' => 'application/json',
            ];

            // For revenue save endpoint, specify JSON content type
            if (strpos($endpoint, '/save') !== false) {
                $requestHeaders['Content-Type'] = 'application/json';
            }

            Log::info('Making API request', [
                'method' => $method,
                'endpoint' => $endpoint,
                'data' => $data,
                'headers' => $requestHeaders,
            ]);

            $startTime = microtime(true);
            $response = Http::withHeaders($requestHeaders)->$method($this->baseUrl.$endpoint, $data);
            $executionTime = microtime(true) - $startTime;

            // Log the API request
            ApiRequest::create([
                'endpoint' => $this->baseUrl.$endpoint,
                'method' => strtoupper($method),
                'request_headers' => $requestHeaders,
                'request_body' => $data,
                'response_status' => $response->status(),
                'response_headers' => $response->headers(),
                'response_body' => $response->json(),
                'execution_time' => $executionTime,
                'error_message' => $response->successful() ? null : $response->body(),
            ]);

            if ($response->status() === 401) { // Token might be expired
                Log::warning('Token expired, attempting to refresh');
                Cache::forget($this->cacheKey); // Clear expired token
                $token = $this->getValidToken();

                if (! $token) {
                    Log::error('Failed to refresh token');

                    return [
                        'success' => false,
                        'error' => 'Authentication required',
                        'status_code' => 401,
                    ];
                }

                // Retry the request with new token - use same content-type logic as above
                $requestHeaders = [
                    'Authorization' => 'Bearer '.$token,
                    'Accept' => 'application/json',
                ];

                // For revenue save endpoint, specify JSON content type
                if (strpos($endpoint, '/save') !== false) {
                    $requestHeaders['Content-Type'] = 'application/json';
                }

                $startTime = microtime(true);
                $response = Http::withHeaders($requestHeaders)->$method($this->baseUrl.$endpoint, $data);
                $executionTime = microtime(true) - $startTime;

                // Log the retry request
                ApiRequest::create([
                    'endpoint' => $this->baseUrl.$endpoint,
                    'method' => strtoupper($method),
                    'request_headers' => $requestHeaders,
                    'request_body' => $data,
                    'response_status' => $response->status(),
                    'response_headers' => $response->headers(),
                    'response_body' => $response->json(),
                    'execution_time' => $executionTime,
                    'error_message' => $response->successful() ? null : $response->body(),
                ]);
            }

            Log::info('API request completed', [
                'method' => $method,
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'execution_time' => $executionTime ?? 'unknown',
            ]);

            if ($response->successful()) {
                Log::info('API request successful', [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'response_data_keys' => array_keys($response->json() ?? []),
                ]);

                return [
                    'success' => true,
                    'data' => $response->json(),
                    'status_code' => $response->status(),
                ];
            }

            // Log more detailed error information
            $responseBody = $response->body();
            $responseData = null;

            // Try to parse the response as JSON
            $parsedResponse = json_decode($responseBody, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $responseData = $parsedResponse;
            } else {
                // If not JSON, store as string
                $responseData = $responseBody;
            }

            Log::error('API request failed', [
                'method' => $method,
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'response_body' => $responseData,
                'request_data' => $data,
            ]);

            // Extract error message from response - try multiple possible keys
            $errorMessage = $responseData['message'] ??
                           $responseData['Message'] ??
                           $responseData['error'] ??
                           'API request failed with status '.$response->status();

            return [
                'success' => false,
                'error' => $errorMessage,
                'status_code' => $response->status(),
                'response_data' => $responseData,
            ];
        } catch (\Exception $e) {
            Log::error('Exception occurred during API request', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Exception occurred during API request: '.$e->getMessage(),
                'status_code' => 500,
            ];
        }
    }

    /**
     * Save revenue data to the API
     * POST - Save Revenue
     * https://tenant.lippomallpuri.com/revenue/api/revenue/v1/save
     */
    public function saveRevenue(array $transactionDatas): array
    {
        $payload = [
            'revenueDatas' => $transactionDatas,
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
            'Number' => $number,
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
            'RevenueBatchId' => $revenueBatchId,
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
            'Years' => $years,
        ];

        return $this->makeRequest('post', '/revenue/api/revenue/v1/getsummary', $payload);
    }
}
