# Payment API Integration Documentation

This document outlines the integration with the Payment API (Lippo Mall Puri Revenue API) in our Laravel application.

## Overview

The integration includes 5 main endpoints:
1. Token - Authentication
2. Save Revenue - Store transaction data
3. Get Revenue - Retrieve transaction data
4. Get Void - Retrieve void transaction data
5. Get Summary - Retrieve monthly transaction summary

## Configuration

Add the following to your `.env` file:

```
PAYMENT_API_BASE_URL=https://tenant.lippomallpuri.com
PAYMENT_API_USERNAME=your_api_username
PAYMENT_API_PASSWORD=your_api_password
```

Add the following to your `config/services.php` file:

```php
'payment_api' => [
    'base_url' => env('PAYMENT_API_BASE_URL'),
    'username' => env('PAYMENT_API_USERNAME'),
    'password' => env('PAYMENT_API_PASSWORD'),
],
```

## Service Class

The main integration is handled by `App\Services\PaymentApiService`.

## Usage Examples

### Getting an API Token

```php
use App\Services\PaymentApiService;

$service = new PaymentApiService();
$result = $service->getToken();

if ($result['success']) {
    $token = $result['token'];
    // Token is automatically cached
}
```

### Saving Revenue

```php
$service = new PaymentApiService();

$transactionData = [
    [
        'TransactionNumber' => 'TRX001',
        'TransactionDate' => '2023-10-01',
        'Amount' => 100.00,
        'Remarks' => 'Test transaction'
    ]
];

$result = $service->saveRevenue($transactionData);

if ($result['success']) {
    $revenueBatchId = $result['data']['RevenueBatchId'];
    // Handle successful save
}
```

### Getting Revenue Data

```php
$service = new PaymentApiService();
$result = $service->getRevenue($revenueBatchId, $page = 1, $number = 0);

if ($result['success']) {
    $revenueData = $result['data'];
    // Process revenue data
}
```

### Getting Void Data

```php
$service = new PaymentApiService();
$result = $service->getVoid($revenueBatchId);

if ($result['success']) {
    $voidData = $result['data'];
    // Process void data
}
```

### Getting Summary Data

```php
$service = new PaymentApiService();
$result = $service->getSummary($months, $years);

if ($result['success']) {
    $summaryData = $result['data'];
    // Process summary data
}
```

## Error Handling

The service includes comprehensive error handling and logging:

- All API requests are logged with method, endpoint, and status
- Errors are logged with detailed information
- Token expiration is automatically handled
- Exceptions are caught and returned with error messages

## Resource Classes

- `TransactionDataResource` - Formats transaction data
- `SaveRevenueResponseResource` - Formats save revenue responses
- `GetRevenueResponseResource` - Formats get revenue responses
- `GetVoidResponseResource` - Formats get void responses
- `GetSummaryResponseResource` - Formats get summary responses