# WioPayments PHP SDK - Vanilla PHP Version

A standalone PHP library for integrating with WioPayments Gateway. **No dependencies required** - works with any PHP project without Composer or external libraries.

## 🚀 Quick Start

### 1. Download and Include

```php
// Simply include the main SDK file
require_once 'WioPayments.php';

// Initialize with your API key
$wioPayments = new WioPayments('your_api_key_here');
```

### 2. Create a Simple Payment

```php
// Create payment data
$paymentData = [
    'amount' => 50.00,
    'currency' => 'USD',
    'order_id' => 'ORDER_' . uniqid(),
    'customer' => [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]
];

// Create payment and get client secret
$payment = $wioPayments->createPayment($paymentData);

// Generate complete payment form
$paymentForm = $wioPayments->renderPaymentForm($paymentData);
file_put_contents('payment.html', $paymentForm);
```

### 3. Check Payment Status

```php
$status = $wioPayments->getPaymentStatus($payment->id);
echo "Payment status: " . $status->status;
```

## 📋 Features

- ✅ **Zero Dependencies** - Pure PHP, no Composer required
- ✅ **Complete Payment Forms** - Ready-to-use HTML with Stripe integration
- ✅ **Hosted Payment Pages** - Redirect customers to secure payment pages
- ✅ **Payment Links** - Create shareable payment URLs
- ✅ **JavaScript Integration** - Add payments to existing pages
- ✅ **Multi-Currency Support** - USD, EUR, TRY, GBP
- ✅ **Real-time Status** - Check payment status anytime
- ✅ **Customizable UI** - Custom CSS and styling options

## 🛠️ Installation Methods

### Method 1: Direct Download (Recommended)
```bash
# Download the single file
wget https://gw.wiopayments.com/sdk/vanilla/WioPayments.php
```

### Method 2: Copy and Paste
Simply copy the `WioPayments.php` file to your project directory.

### Method 3: Include in Existing Projects
```php
// Add to your existing PHP application
include_once '/path/to/WioPayments.php';
```

## 📚 Usage Examples

### Complete Payment Form

```php
<?php
require_once 'WioPayments.php';

$wioPayments = new WioPayments('your_api_key');

$paymentData = [
    'amount' => 99.99,
    'currency' => 'USD',
    'order_id' => 'ORDER_123',
    'description' => 'Premium Product',
    'customer' => [
        'name' => 'Customer Name',
        'email' => 'customer@email.com'
    ]
];

// Generate complete payment page
$paymentForm = $wioPayments->renderPaymentForm($paymentData, [
    'submit_button_text' => 'Pay $99.99',
    'success_url' => 'https://yoursite.com/success',
    'custom_css' => '.wiopayments-button { background: #28a745; }'
]);

echo $paymentForm;
?>
```

### Hosted Payment Redirect

```php
<?php
require_once 'WioPayments.php';

$wioPayments = new WioPayments('your_api_key');

$sessionData = [
    'amount' => 75.00,
    'currency' => 'USD',
    'order_id' => 'ORDER_456',
    'success_url' => 'https://yoursite.com/success',
    'cancel_url' => 'https://yoursite.com/cancel',
    'customer' => [
        'name' => 'Jane Smith',
        'email' => 'jane@email.com'
    ]
];

// Get payment URL and redirect
$paymentUrl = $wioPayments->createHostedPaymentUrl($sessionData);
header('Location: ' . $paymentUrl);
exit;
?>
```

### Existing Page Integration

```php
<?php
require_once 'WioPayments.php';

$wioPayments = new WioPayments('your_api_key');

// Create payment
$payment = $wioPayments->createPayment($paymentData);

// Get JavaScript code
$script = $wioPayments->generatePaymentScript($payment->id);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Existing Page</title>
</head>
<body>
    <h1>Your Product</h1>
    <div id="payment-container"></div>

    <script>
        <?php echo $script; ?>
        
        // Your existing JavaScript
        window.wioPaymentsReady = function() {
            const paymentForm = window.WioPayments.createPaymentForm('payment-container');
            // Handle payment confirmation...
        };
    </script>
</body>
</html>
```

### Payment Links

```php
<?php
require_once 'WioPayments.php';

$wioPayments = new WioPayments('your_api_key');

// Create payment link
$linkData = [
    'amount' => 25.00,
    'currency' => 'USD',
    'description' => 'Digital Product',
    'max_uses' => 1,
    'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days'))
];

$paymentLink = $wioPayments->createPaymentLink($linkData);

echo "Share this payment link: " . $paymentLink['url'];

// List all payment links
$allLinks = $wioPayments->listPaymentLinks();
foreach ($allLinks['data'] as $link) {
    echo $link['description'] . ": " . $link['url'] . "\n";
}
?>
```

## 🔧 Configuration Options

```php
$wioPayments = new WioPayments('your_api_key', [
    'base_url' => 'https://gw.wiopayments.com',
    'timeout' => 30,
    'verify_ssl' => true
]);
```

## 🎨 Customization

### Custom Payment Form Styling

```php
$paymentForm = $wioPayments->renderPaymentForm($paymentData, [
    'custom_css' => '
        .wiopayments-form {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }
        .wiopayments-button {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border-radius: 25px;
        }
        .wiopayments-card-element {
            border: 2px solid #007bff;
        }
    '
]);
```

### Form Customization Options

```php
$options = [
    'submit_button_text' => 'Complete Purchase',
    'loading_button_text' => 'Processing Payment...',
    'success_url' => 'https://yoursite.com/thank-you',
    'cancel_url' => 'https://yoursite.com/checkout',
    'form_id' => 'my-payment-form',
    'card_element_id' => 'my-card-element',
    'error_element_id' => 'my-error-element'
];
```

## 🔒 Security

- All payments are processed securely through Stripe
- No sensitive payment data touches your server
- PCI DSS compliant by design
- SSL/TLS encryption for all API communication

## 💰 Supported Currencies

- USD (US Dollar)
- EUR (Euro)
- TRY (Turkish Lira)
- GBP (British Pound)

## 📱 Browser Support

- Chrome 60+
- Firefox 60+
- Safari 11+
- Edge 16+
- Mobile browsers (iOS Safari, Chrome Mobile)

## 🆘 Error Handling

```php
try {
    $payment = $wioPayments->createPayment($paymentData);
    echo "Payment created: " . $payment->id;
} catch (WioPaymentsException $e) {
    echo "Payment error: " . $e->getMessage();
    echo "Error code: " . $e->getCode();
} catch (Exception $e) {
    echo "Unexpected error: " . $e->getMessage();
}
```

## 📊 Payment Status Values

- `pending` - Payment created, awaiting customer action
- `processing` - Payment is being processed
- `succeeded` - Payment completed successfully
- `failed` - Payment failed or was declined
- `canceled` - Payment was canceled by customer

## 🔗 API Endpoints

The vanilla PHP SDK communicates with these WioPayments API endpoints:

- `POST /api/v1/create-payment` - Create new payment
- `GET /api/v1/payment/{id}/status` - Get payment status
- `POST /api/v1/hosted/sessions` - Create hosted payment session
- `POST /api/v1/payment-links` - Create payment link
- `GET /api/v1/payment-links` - List payment links

## 🧪 Testing

### Test API Key
```
wio_KTXPsDbGOBDCjQGA5axcIR0JJy2E9Pkj
```

### Test Cards
```
Success: 4242424242424242
Declined: 4000000000000002
```

### Test Environment
```
https://gw.wiopayments.com/test-payment.html
```

## 🆔 Version Information

- **Version**: 1.0.0
- **PHP Requirements**: PHP 7.4+
- **Dependencies**: None (uses cURL for HTTP requests)
- **License**: MIT

## 🚀 Migration from Composer Version

If you're currently using the Composer version, migration is simple:

```php
// Old way (Composer)
use WioPayments\Client;
$client = new Client('api_key');

// New way (Vanilla)
require_once 'WioPayments.php';
$client = new WioPayments('api_key');

// All methods remain the same!
$payment = $client->createPayment($data);
```

## 💡 Tips and Best Practices

1. **Always validate payment data** before creating payments
2. **Use HTTPS** in production for security
3. **Store payment IDs** for transaction tracking
4. **Handle errors gracefully** with try-catch blocks
5. **Test with small amounts** before going live
6. **Keep your API key secure** and never expose it in frontend code

## 📞 Support

- 📧 Email: support@wiopayments.com
- 📖 Documentation: https://docs.wiopayments.com
- 🌐 Test Environment: https://gw.wiopayments.com/test-payment.html

---

**Ready to start accepting payments?** 

1. Get your API key from the WioPayments dashboard
2. Download `WioPayments.php`
3. Follow the quick start guide above
4. Start processing payments in minutes!

*No credit card required for testing. Use our test API key and test cards to get started immediately.*