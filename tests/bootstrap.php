<?php

// Bootstrap file for PHPUnit tests

// Include the WioPayments SDK
require_once __DIR__ . '/../src/WioPayments.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('UTC');

// Test constants
define('WIOPAYMENTS_TEST_API_KEY', 'wio_KTXPsDbGOBDCjQGA5axcIR0JJy2E9Pkj');
define('WIOPAYMENTS_TEST_BASE_URL', 'https://gw.wiopayments.com');

// Helper functions for tests
function createTestPaymentData(array $overrides = []): array
{
    return array_merge([
        'amount' => 50.00,
        'currency' => 'USD',
        'order_id' => 'TEST_' . uniqid(),
        'description' => 'Test Payment',
        'customer' => [
            'name' => 'Test Customer',
            'email' => 'test@example.com'
        ]
    ], $overrides);
}

function createTestClient(array $config = []): WioPayments
{
    return new WioPayments(WIOPAYMENTS_TEST_API_KEY, array_merge([
        'base_url' => WIOPAYMENTS_TEST_BASE_URL,
        'timeout' => 30,
        'verify_ssl' => false // For testing
    ], $config));
}