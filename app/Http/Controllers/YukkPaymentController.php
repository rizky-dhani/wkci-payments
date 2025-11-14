<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\QRCodeService;
use App\Services\YukkPaymentService;
use App\Services\WooCommerceService;
use App\Jobs\SendPaymentEmailJob;
use App\Http\Requests\GenerateQRISRequest;
use App\Http\Requests\QueryPaymentRequest;
use App\Mail\PendingPayment;
use App\Mail\SuccessPayment;
use Milon\Barcode\DNS2D;
use Automattic\WooCommerce\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use DateTime;
use DateTimeZone;

class YukkPaymentController extends Controller
{
    public function __construct(
        private YukkPaymentService $yukkService,
        private QRCodeService $qrService,
        private WooCommerceService $wooCommerceService
    ) {}

    /**
     * Generate QRIS payment
     */
    public function generateQRIS(GenerateQRISRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            
            $result = $this->yukkService->generateQRIS([
                'transaction_id' => $validated['trx_id'],
                'amount' => $validated['amount']
            ]);

            $qrWeb = $this->qrService->generateQRCodeHTML($result['qrContent']);

            // Queue email sending
            SendPaymentEmailJob::dispatch(
                $validated['customer_email'],
                'pending',
                $validated['trx_id'],
                $validated['customer_name'],
                $validated['amount'],
                $result
            );

            DB::commit();

            return view('generated-qr', compact('result', 'qrWeb'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('QRIS generation failed', [
                'error' => $e->getMessage(),
                'request' => $request->validated()
            ]);
            
            return back()->withErrors('Payment generation failed. Please try again.');
        }
    }

    /**
     * Query payment status from cache
     */
    public function queryPayment()
    {
        try {
            $transactionData = Cache::get('transactionData');
            
            if (!$transactionData) {
                return back()->withErrors('Transaction data not found.');
            }

            $transaction = Transaction::select('customer_name', 'customer_email', 'trx_order_no', 'partner_ref_no', 'qrCode')
                ->where('transactionId', $transactionData['transactionId'])
                ->first();

            if (!$transaction) {
                return back()->withErrors('Transaction not found.');
            }

            return $this->processPaymentQuery($transaction, $transactionData);
        } catch (\Exception $e) {
            Log::error('Payment query failed', ['error' => $e->getMessage()]);
            return back()->withErrors('Payment query failed. Please try again.');
        }
    }

    /**
     * Query payment status from email link
     */
    public function queryPaymentFromEmail(QueryPaymentRequest $request)
    {
        try {
            $transactionId = base64_decode($request->validated()['trxId']);
            
            $transaction = Transaction::where('transactionId', $transactionId)->first();
            
            if (!$transaction) {
                return back()->withErrors('Transaction not found.');
            }

            $transactionData = [
                'transactionId' => $transactionId,
                'amount' => $transaction->amount
            ];

            return $this->processPaymentQuery($transaction, $transactionData);
        } catch (\Exception $e) {
            Log::error('Payment query from email failed', ['error' => $e->getMessage()]);
            return back()->withErrors('Payment query failed. Please try again.');
        }
    }

    /**
     * Process payment query logic (shared between methods)
     */
    private function processPaymentQuery(Transaction $transaction, array $transactionData)
    {
        try {
            $queryResult = $this->yukkService->queryPaymentStatus($transaction->partner_ref_no);

            if ($queryResult['transactionStatusDesc'] === 'Paid') {
                return $this->handleSuccessfulPayment($transaction, $transactionData, $queryResult);
            }

            $qr = $this->qrService->generateQRCodeHTML($transaction->qrCode ?? '');
            
            return view('query-payment', [
                'order_id' => base64_encode($transaction->trx_order_no),
                'qr' => $qr,
                'queryResult' => $queryResult,
            ]);
        } catch (\Exception $e) {
            Log::error('Process payment query failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Handle successful payment processing
     */
    private function handleSuccessfulPayment(Transaction $transaction, array $transactionData, array $queryResult)
    {
        try {
            DB::beginTransaction();

            // Update WooCommerce order
            $this->wooCommerceService->completeOrder($transaction->trx_order_no);

            // Update transaction
            Transaction::where('transactionId', $transactionData['transactionId'])->update([
                'payment_status' => $queryResult['transactionStatusDesc'],
                'paid_at' => $queryResult['paidTime'] ?? now(),
            ]);

            // Queue success email
            SendPaymentEmailJob::dispatch(
                $transaction->customer_email,
                'success',
                $transactionData['transactionId'],
                $transaction->customer_name,
                $transactionData['amount'],
                $queryResult
            );

            // Clear cache
            Cache::forget('generateQrResult');
            Cache::forget('trxId');
            Cache::forget('transactionData');
            Cache::put('successPayment', $queryResult);

            DB::commit();

            return view('payment-successful');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Handle successful payment failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Generate access token for YUKK (API endpoint)
     */
    public function generateAccessTokenForYUKK()
    {
        try {
            $signature = request()->header('X-SIGNATURE') ?? '';
            $clientID = request()->header('X-CLIENT-KEY') ?? '';
            $timestamp = request()->header('X-TIMESTAMP') ?? '';
            $grantType = request()->input('grantType') ?? '';

            // Validation
            $validation = $this->validateYukkRequest($signature, $clientID, $timestamp, $grantType);
            if ($validation !== true) {
                return $validation;
            }

            $accessToken = str()->random(983);
            Cache::put('accessTokenYUKK', $accessToken, now()->addSeconds(900));

            return response()->json([
                'responseCode' => '2007300',
                'responseMessage' => 'Successful',
                'accessToken' => $accessToken,
                'tokenType' => 'Bearer',
                'expiresIn' => '900'
            ]);
        } catch (\Exception $e) {
            Log::error('Generate access token for YUKK failed', ['error' => $e->getMessage()]);
            return response()->json([
                'responseCode' => '5007300',
                'responseMessage' => 'Internal Server Error'
            ], 500);
        }
    }

    /**
     * Payment notification endpoint
     */
    public function paymentNotification()
    {
        try {
            $this->generateAccessTokenForYUKK();
            $accessToken = Cache::get('accessTokenYUKK');
            $queryResult = Cache::get('successPayment');

            // Build notification body
            $body = $this->buildNotificationBody($queryResult);

            // Validate required fields
            $validation = $this->validateNotificationBody($body);
            if ($validation !== true) {
                return $validation;
            }

            return response()->json([
                'responseCode' => '2005200',
                'responseMessage' => 'Successful'
            ]);
        } catch (\Exception $e) {
            Log::error('Payment notification failed', ['error' => $e->getMessage()]);
            return response()->json([
                'responseCode' => '5005200',
                'responseMessage' => 'Internal Server Error'
            ], 500);
        }
    }

    /**
     * Validate YUKK request parameters
     */
    private function validateYukkRequest(string $signature, string $clientID, string $timestamp, string $grantType)
    {
        $expectedClientId = config('yukk.client_id');
        $timestampFormat = (new DateTime())->format('c');

        if ($clientID !== $expectedClientId) {
            return response()->json([
                'responseCode' => '4007300',
                'responseMessage' => 'Unauthorized. Invalid Client ID'
            ], 401);
        }

        if (!$grantType) {
            return response()->json([
                'responseCode' => '4007302',
                'responseMessage' => 'Invalid Mandatory Field grantType'
            ], 400);
        }

        // Additional signature validation would go here
        return true;
    }

    /**
     * Build notification request body
     */
    private function buildNotificationBody(?array $queryResult): array
    {
        $isSuccess = ($queryResult['latestTransactionStatus'] ?? '') === '00';

        return [
            'originalReferenceNo' => request()->input('originalReferenceNo') ?? $queryResult['originalReferenceNo'] ?? '',
            'originalPartnerReferenceNo' => request()->input('originalPartnerReferenceNo') ?? $queryResult['originalPartnerReferenceNo'] ?? '',
            'latestTransactionStatus' => request()->input('latestTransactionStatus') ?? $queryResult['latestTransactionStatus'] ?? '',
            'transactionStatusDesc' => request()->input('transactionStatusDesc') ?? $queryResult['transactionStatusDesc'] ?? '',
            'amount' => [
                'value' => request()->input('value') ?? $queryResult['amount']['value'] ?? '',
                'currency' => 'IDR',
            ],
            'externalStoreId' => request()->input('externalStoreId') ?? config('yukk.store_id'),
            'additionalInfo' => [
                'additionalField' => request()->input('additionalField') ?? $queryResult['additionalInfo']['additionalField'] ?? [],
                'rrn' => '210430233071'
            ]
        ];
    }

    /**
     * Validate notification body fields
     */
    private function validateNotificationBody(array $body)
    {
        $requiredFields = [
            'originalReferenceNo' => 'Invalid Mandatory Field originalReferenceNo',
            'latestTransactionStatus' => 'Invalid Mandatory Field latestTransactionStatus',
            'transactionStatusDesc' => 'Invalid Mandatory Field transactionStatusDesc',
            'amount.value' => 'Invalid Mandatory Field amount.value',
            'additionalInfo.rrn' => 'Invalid Mandatory Field additionalInfo.rrn'
        ];

        foreach ($requiredFields as $field => $message) {
            $value = data_get($body, $field);
            if (empty($value)) {
                return response()->json([
                    'responseCode' => '4005202',
                    'responseMessage' => $message
                ], 400);
            }
        }

        if ($body['externalStoreId'] !== config('yukk.store_id')) {
            return response()->json([
                'responseCode' => '4005202',
                'responseMessage' => 'Invalid Mandatory Field externalStoreId'
            ], 400);
        }

        return true;
    }
}
