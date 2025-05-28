<?php
/**
 * WioPayments Vanilla PHP SDK - Hosted Payment Example
 * 
 * This example shows how to create a hosted payment session and redirect users
 * to the WioPayments hosted payment page
 */

// Include the WioPayments SDK
require_once __DIR__ . '/../WioPayments.php';

// Initialize WioPayments client
$wioPayments = new WioPayments('wio_KTXPsDbGOBDCjQGA5axcIR0JJy2E9Pkj');

try {
    // Create hosted payment session data
    $sessionData = [
        'amount' => 75.00, // $75.00
        'currency' => 'USD',
        'order_id' => 'HOSTED_' . uniqid(),
        'description' => 'Premium Service Payment',
        'success_url' => 'https://yoursite.com/payment/success',
        'cancel_url' => 'https://yoursite.com/payment/cancel',
        'customer' => [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com'
        ],
        'metadata' => [
            'product_id' => 'premium_service',
            'user_id' => '12345'
        ]
    ];

    // Method 1: Create hosted payment session step by step
    echo "🔧 Creating hosted payment session...\n";
    $session = $wioPayments->createHostedPaymentSession($sessionData);
    
    echo "✅ Hosted payment session created!\n";
    echo "Session ID: " . $session['session_id'] . "\n";
    echo "Payment URL: " . $session['payment_url'] . "\n";
    echo "Expires at: " . $session['expires_at'] . "\n";

    // Method 2: Create payment URL directly (shortcut)
    echo "\n🚀 Creating direct payment URL...\n";
    $paymentUrl = $wioPayments->createHostedPaymentUrl($sessionData);
    echo "Direct Payment URL: " . $paymentUrl . "\n";

    // Generate HTML redirect page
    $redirectHtml = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Redirecting to Payment...</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background: #f8f9fa;
        }
        .redirect-container {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #007bff;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
    </style>
    <script>
        // Auto redirect after 3 seconds
        setTimeout(function() {
            window.location.href = "' . $paymentUrl . '";
        }, 3000);
    </script>
</head>
<body>
    <div class="redirect-container">
        <h2>Redirecting to Payment</h2>
        <div class="spinner"></div>
        <p>Please wait while we redirect you to the secure payment page...</p>
        <p><strong>Amount:</strong> $' . number_format($sessionData['amount'], 2) . ' ' . strtoupper($sessionData['currency']) . '</p>
        <p><strong>Order:</strong> ' . $sessionData['order_id'] . '</p>
        <a href="' . $paymentUrl . '" class="btn">Continue to Payment</a>
    </div>
</body>
</html>';

    // Save redirect page
    file_put_contents('hosted-payment-redirect.html', $redirectHtml);
    echo "🌐 Redirect page saved to hosted-payment-redirect.html\n";

    // Check session status
    $sessionStatus = $wioPayments->getHostedSessionStatus($session['session_id']);
    echo "\n📊 Session Status: " . $sessionStatus['status'] . "\n";

} catch (WioPaymentsException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
} catch (Exception $e) {
    echo "❌ Unexpected error: " . $e->getMessage() . "\n";
}

echo "\n💡 Usage Tips:\n";
echo "1. Open hosted-payment-redirect.html in browser to test\n";
echo "2. Use the payment URL directly for immediate redirect\n";
echo "3. Customize success_url and cancel_url for your application\n";