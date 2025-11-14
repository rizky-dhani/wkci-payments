<?php

namespace App\Services;

use DateTime;
use DateTimeZone;
use App\Models\Transaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class YukkPaymentService
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;
    private string $storeId;

    public function __construct()
    {
        $this->baseUrl = config('yukk.base_url', 'https://snapqris.yukk.co.id');
        $this->clientId = config('yukk.client_id', '') ?: '';
        $this->clientSecret = config('yukk.client_secret', '') ?: '';
        $this->storeId = config('yukk.store_id', '') ?: '';
    }

    /**
     * Generate timestamp in ISO 8601 format
     */
    public function generateTimestamp(): string
    {
        return (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format('c');
    }

    /**
     * Generate unique partner reference number
     */
    public function generatePartnerReferenceNo(): string
    {
        $partnerRefNo = 'SNAP_QRIS_WKCI_' . uniqid();
        Cache::put('partnerRefNo', $partnerRefNo, now()->addMinutes(2));
        return $partnerRefNo;
    }

    /**
     * Generate access token with caching
     */
    public function generateAccessToken(): array
    {
        return Cache::remember('yukk_access_token', 840, function () {
            try {
                $endpoint = "/1.0/access-token/b2b";
                $timestamp = $this->generateTimestamp();
                $stringToSign = $this->clientId . '|' . $timestamp;

                $signature = $this->createSignature($stringToSign);

                $headers = [
                    'Content-Type' => 'application/json',
                    'X-TIMESTAMP' => $timestamp,
                    'X-CLIENT-KEY' => $this->clientId,
                    'X-SIGNATURE' => $signature,
                ];

                $body = ['grantType' => 'client_credentials'];

                $response = Http::withHeaders($headers)
                    ->post($this->baseUrl . $endpoint, $body);

                if ($response->failed()) {
                    throw new \Exception('Failed to generate access token: ' . $response->body());
                }

                return $response->json();
            } catch (\Exception $e) {
                Log::error('Access token generation failed', ['error' => $e->getMessage()]);
                throw $e;
            }
        });
    }

    /**
     * Generate QRIS payment
     */
    public function generateQRIS(array $paymentData): array
    {
        try {
            $endpoint = "/1.0/qr/qr-mpm-generate";
            $timestamp = $this->generateTimestamp();
            $accessToken = $this->generateAccessToken()['accessToken'];
            $partnerRefNo = $this->generatePartnerReferenceNo();

            $requestBody = [
                'partnerReferenceNo' => $partnerRefNo,
                'amount' => [
                    'value' => number_format($paymentData['amount'], 2, '.', ''),
                    'currency' => 'IDR'
                ],
                'feeAmount' => [
                    'value' => '0.00',
                    'currency' => 'IDR'
                ],
                'storeId' => $this->storeId,
                'additionalInfo' => [
                    'additionalField' => [
                        'merchantId' => 'SAI'
                    ]
                ]
            ];

            $headers = $this->buildRequestHeaders($accessToken, $requestBody, $endpoint, $timestamp);

            $response = Http::withHeaders($headers)
                ->post($this->baseUrl . $endpoint, $requestBody);

            if ($response->failed()) {
                throw new \Exception('Failed to generate QRIS: ' . $response->body());
            }

            $result = $response->json();

            // Update transaction
            Transaction::where('transactionId', $paymentData['transaction_id'])->update([
                'partner_ref_no' => $result['partnerReferenceNo'],
                'qrCode' => $result['qrContent']
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('QRIS generation failed', [
                'error' => $e->getMessage(),
                'payment_data' => $paymentData
            ]);
            throw $e;
        }
    }

    /**
     * Query payment status
     */
    public function queryPaymentStatus(string $partnerRefNo): array
    {
        try {
            $endpoint = "/1.0/qr/qr-mpm-query";
            $accessToken = $this->generateAccessToken()['accessToken'];
            $timestamp = $this->generateTimestamp();

            $requestBody = [
                'originalPartnerReferenceNo' => $partnerRefNo,
                'serviceCode' => '47',
                'externalStoreId' => $this->storeId
            ];

            $headers = $this->buildRequestHeaders($accessToken, $requestBody, $endpoint, $timestamp);

            $response = Http::withHeaders($headers)
                ->post($this->baseUrl . $endpoint, $requestBody);

            if ($response->failed()) {
                throw new \Exception('Failed to query payment status: ' . $response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Payment query failed', [
                'error' => $e->getMessage(),
                'partner_ref_no' => $partnerRefNo
            ]);
            throw $e;
        }
    }

    /**
     * Create RSA signature
     */
    private function createSignature(string $stringToSign): string
    {
        $privateKeyPath = storage_path('app/private/wkci_private.pem');
        
        if (!file_exists($privateKeyPath)) {
            throw new \Exception('Private key file not found');
        }

        $privateKeyContent = file_get_contents($privateKeyPath);
        $privateKey = openssl_pkey_get_private($privateKeyContent);

        if (!$privateKey) {
            throw new \Exception('Invalid private key');
        }

        openssl_sign($stringToSign, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        
        return base64_encode($signature);
    }

    /**
     * Build request headers for API calls
     */
    private function buildRequestHeaders(string $accessToken, array $requestBody, string $endpoint, string $timestamp): array
    {
        $minifiedBody = json_encode($requestBody);
        $stringToSign = "POST:" . $endpoint . ":" . $accessToken . ":" . 
                       strtolower(hash("sha256", $minifiedBody)) . ":" . $timestamp;
        
        $symmetricSignature = base64_encode(
            hash_hmac('sha512', $stringToSign, $this->clientSecret, true)
        );

        $unique = random_int(1000000000000, 9999999999999);

        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => $symmetricSignature,
            'X-PARTNER-ID' => $this->clientId,
            'X-EXTERNAL-ID' => $unique,
            'CHANNEL-ID' => '00001'
        ];
    }
}
