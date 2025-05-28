<?php
/**
 * WioPayments Vanilla PHP SDK - Simple Payment Example
 * 
 * This example shows how to create a simple payment using the vanilla PHP SDK
 * No Composer or external dependencies required - just include the WioPayments.php file
 */

// Include the WioPayments SDK
require_once __DIR__ . '/../WioPayments.php';

// Initialize WioPayments client with your API key
$wioPayments = new WioPayments('wio_KTXPsDbGOBDCjQGA5axcIR0JJy2E9Pkj', [
    'base_url' => 'https://gw.wiopayments.com',
    'timeout' => 30,
    'verify_ssl' => true
]);

try {
    // Create payment data
    $paymentData = [
        'amount' => 50.00, // $50.00
        'currency' => 'USD',
        'order_id' => 'ORDER_' . uniqid(),
        'description' => 'Test Payment from Vanilla PHP SDK',
        'customer' => [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]
    ];

    // Create the payment
    $payment = $wioPayments->createPayment($paymentData);

    echo "✅ Payment created successfully!\n";
    echo "Payment ID: " . $payment->id . "\n";
    echo "Amount: $" . number_format($payment->amount, 2) . " " . strtoupper($payment->currency) . "\n";
    echo "Status: " . $payment->status . "\n";
    echo "Order ID: " . $payment->order_id . "\n";

    // Generate complete payment form HTML
    $paymentForm = $wioPayments->renderPaymentForm($paymentData, [
        'submit_button_text' => 'Pay $50.00',
        'success_url' => 'https://yoursite.com/payment/success',
        'cancel_url' => 'https://yoursite.com/payment/cancel',
        'custom_css' => '
            .wiopayments-form {
                background: #f8f9fa;
                border: 1px solid #dee2e6;
                border-radius: 8px;
            }
            .wiopayments-button {
                background: #28a745;
            }
        '
    ]);

    // Save the payment form to a file
    file_put_contents('payment-form.html', $paymentForm);
    echo "💳 Payment form HTML generated and saved to payment-form.html\n";
    echo "📝 Open payment-form.html in your browser to test the payment\n";

    // Check payment status
    $status = $wioPayments->getPaymentStatus($payment->id);
    echo "📊 Current payment status: " . $status->status . "\n";

} catch (WioPaymentsException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
} catch (Exception $e) {
    echo "❌ Unexpected error: " . $e->getMessage() . "\n";
}