<?php
/**
 * WioPayments Vanilla PHP SDK - Payment Links Example
 * 
 * This example demonstrates how to create and manage payment links
 * Perfect for invoicing, email payments, and shareable payment URLs
 */

// Include the WioPayments SDK
require_once __DIR__ . '/../WioPayments.php';

// Initialize WioPayments client
$wioPayments = new WioPayments('wio_KTXPsDbGOBDCjQGA5axcIR0JJy2E9Pkj');

try {
    echo "🔗 WioPayments Payment Links Demo\n";
    echo "==================================\n\n";

    // Example 1: Simple one-time payment link
    echo "1️⃣ Creating simple payment link...\n";
    $simpleLinkData = [
        'amount' => 25.00,
        'currency' => 'USD',
        'description' => 'Digital Download - eBook',
        'max_uses' => 1, // One-time use
        'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days')), // Expires in 7 days
        'metadata' => [
            'product_type' => 'digital',
            'category' => 'ebook'
        ]
    ];

    $simpleLink = $wioPayments->createPaymentLink($simpleLinkData);
    echo "✅ Simple payment link created!\n";
    echo "Link ID: " . $simpleLink['id'] . "\n";
    echo "Payment URL: " . $simpleLink['url'] . "\n";
    echo "Expires: " . $simpleLink['expires_at'] . "\n\n";

    // Example 2: Recurring subscription link
    echo "2️⃣ Creating subscription payment link...\n";
    $subscriptionLinkData = [
        'amount' => 29.99,
        'currency' => 'USD',
        'description' => 'Monthly Subscription - Premium Plan',
        'max_uses' => 100, // Allow multiple uses
        'success_url' => 'https://yoursite.com/subscription/welcome',
        'cancel_url' => 'https://yoursite.com/subscription/cancelled',
        'metadata' => [
            'plan_type' => 'premium',
            'billing_cycle' => 'monthly'
        ]
    ];

    $subscriptionLink = $wioPayments->createPaymentLink($subscriptionLinkData);
    echo "✅ Subscription payment link created!\n";
    echo "Link ID: " . $subscriptionLink['id'] . "\n";
    echo "Payment URL: " . $subscriptionLink['url'] . "\n\n";

    // Example 3: Invoice payment link with custom fields
    echo "3️⃣ Creating invoice payment link...\n";
    $invoiceLinkData = [
        'amount' => 150.00,
        'currency' => 'USD',
        'description' => 'Consulting Services - Invoice #INV-2024-001',
        'max_uses' => 1,
        'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
        'success_url' => 'https://yoursite.com/invoices/paid',
        'metadata' => [
            'invoice_number' => 'INV-2024-001',
            'client_id' => 'CLIENT_789',
            'service_type' => 'consulting'
        ]
    ];

    $invoiceLink = $wioPayments->createPaymentLink($invoiceLinkData);
    echo "✅ Invoice payment link created!\n";
    echo "Link ID: " . $invoiceLink['id'] . "\n";
    echo "Payment URL: " . $invoiceLink['url'] . "\n\n";

    // List all payment links
    echo "📋 Listing all payment links...\n";
    $allLinks = $wioPayments->listPaymentLinks();
    echo "Total payment links: " . count($allLinks['data']) . "\n\n";

    foreach ($allLinks['data'] as $link) {
        echo "🔗 " . $link['description'] . "\n";
        echo "   Amount: $" . number_format($link['amount'], 2) . " " . strtoupper($link['currency']) . "\n";
        echo "   Status: " . $link['status'] . "\n";
        echo "   Uses: " . $link['used_count'] . "/" . ($link['max_uses'] ?? '∞') . "\n";
        echo "   URL: " . $link['url'] . "\n\n";
    }

    // Get specific payment link details
    echo "🔍 Getting payment link details...\n";
    $linkDetails = $wioPayments->getPaymentLink($simpleLink['id']);
    echo "Link Status: " . $linkDetails['status'] . "\n";
    echo "Created: " . $linkDetails['created_at'] . "\n";
    echo "Used Count: " . $linkDetails['used_count'] . "\n\n";

    // Generate shareable HTML for payment links
    $shareableHtml = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Links - WioPayments Demo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f8f9fa;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .payment-link {
            background: white;
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 4px solid #007bff;
        }
        .amount {
            font-size: 1.5em;
            color: #28a745;
            font-weight: bold;
        }
        .description {
            color: #6c757d;
            margin: 10px 0;
        }
        .pay-button {
            background: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
            font-size: 16px;
        }
        .pay-button:hover {
            background: #0056b3;
        }
        .meta-info {
            font-size: 0.9em;
            color: #6c757d;
            margin-top: 15px;
        }
        .expires {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>💳 Payment Links</h1>
        <p>Secure payment options powered by WioPayments</p>
    </div>

    <div class="payment-link">
        <h3>📚 Digital eBook</h3>
        <div class="amount">$' . number_format($simpleLink['amount'], 2) . ' ' . strtoupper($simpleLink['currency']) . '</div>
        <div class="description">' . $simpleLink['description'] . '</div>
        <a href="' . $simpleLink['url'] . '" class="pay-button">Buy Now</a>
        <div class="meta-info">
            <div class="expires">⏰ Expires: ' . date('M j, Y', strtotime($simpleLink['expires_at'])) . '</div>
            <div>🔒 Secure payment via WioPayments</div>
        </div>
    </div>

    <div class="payment-link">
        <h3>⭐ Premium Subscription</h3>
        <div class="amount">$' . number_format($subscriptionLink['amount'], 2) . ' ' . strtoupper($subscriptionLink['currency']) . '/month</div>
        <div class="description">' . $subscriptionLink['description'] . '</div>
        <a href="' . $subscriptionLink['url'] . '" class="pay-button">Subscribe Now</a>
        <div class="meta-info">
            <div>🔄 Monthly billing</div>
            <div>🔒 Cancel anytime</div>
        </div>
    </div>

    <div class="payment-link">
        <h3>📄 Consulting Invoice</h3>
        <div class="amount">$' . number_format($invoiceLink['amount'], 2) . ' ' . strtoupper($invoiceLink['currency']) . '</div>
        <div class="description">' . $invoiceLink['description'] . '</div>
        <a href="' . $invoiceLink['url'] . '" class="pay-button">Pay Invoice</a>
        <div class="meta-info">
            <div class="expires">⏰ Due: ' . date('M j, Y', strtotime($invoiceLink['expires_at'])) . '</div>
            <div>📧 Questions? Contact us at billing@yoursite.com</div>
        </div>
    </div>

    <div style="text-align: center; margin-top: 40px; color: #6c757d;">
        <p>All payments are processed securely by WioPayments</p>
        <p>🔒 SSL encrypted • 💳 Major cards accepted • 🔄 Instant confirmation</p>
    </div>
</body>
</html>';

    // Save the shareable page
    file_put_contents('payment-links-demo.html', $shareableHtml);
    echo "🌐 Shareable payment links page saved to: payment-links-demo.html\n";

    // Generate QR codes (simple text-based representation)
    echo "\n📱 QR Code URLs (for generating actual QR codes):\n";
    echo "Simple Payment: https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($simpleLink['url']) . "\n";
    echo "Subscription: https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($subscriptionLink['url']) . "\n";
    echo "Invoice: https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($invoiceLink['url']) . "\n";

} catch (WioPaymentsException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Unexpected error: " . $e->getMessage() . "\n";
}

echo "\n💡 Payment Links Use Cases:\n";
echo "• 📧 Email invoices to customers\n";
echo "• 💬 Share payment links via SMS/WhatsApp\n";
echo "• 🌐 Embed in websites or social media\n";
echo "• 📱 Generate QR codes for offline payments\n";
echo "• 🔄 Create recurring subscription links\n";
echo "• 📊 Track payment link performance\n";