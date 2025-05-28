<?php
/**
 * WioPayments Vanilla PHP SDK - Existing Page Integration Example
 * 
 * This example shows how to integrate WioPayments into your existing HTML pages
 * using JavaScript injection and custom elements
 */

// Include the WioPayments SDK
require_once __DIR__ . '/../WioPayments.php';

// Initialize WioPayments client
$wioPayments = new WioPayments('wio_KTXPsDbGOBDCjQGA5axcIR0JJy2E9Pkj');

try {
    // Create payment
    $paymentData = [
        'amount' => 99.99,
        'currency' => 'USD',
        'order_id' => 'EXISTING_' . uniqid(),
        'description' => 'Product Purchase - Integration Test',
        'customer' => [
            'name' => 'Integration Test User',
            'email' => 'test@yoursite.com'
        ]
    ];

    $payment = $wioPayments->createPayment($paymentData);
    
    // Generate JavaScript for existing pages
    $paymentScript = $wioPayments->generatePaymentScript($payment->id);

    // Create example existing page with integrated payment
    $existingPageHtml = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Existing Website - Product Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
        }
        .product-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .product-image {
            background: #ecf0f1;
            height: 300px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #7f8c8d;
        }
        .product-details {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .price {
            font-size: 2em;
            color: #e74c3c;
            font-weight: bold;
            margin: 15px 0;
        }
        .payment-section {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 30px;
        }
        .payment-container {
            max-width: 400px;
            margin: 20px auto;
        }
        #wio-payment-form {
            border: 2px dashed #bdc3c7;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            color: #7f8c8d;
        }
        .btn {
            background: #3498db;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px;
        }
        .btn:hover {
            background: #2980b9;
        }
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🛍️ Your Online Store</h1>
        <p>Premium Products - Secure Payments</p>
    </div>

    <div class="product-container">
        <div class="product-image">
            📱 Product Image
        </div>
        <div class="product-details">
            <h2>Premium Smartphone</h2>
            <p>Latest model with advanced features and premium build quality. Perfect for professionals and tech enthusiasts.</p>
            
            <div class="price">$' . number_format($payment->amount, 2) . '</div>
            
            <ul>
                <li>✅ 128GB Storage</li>
                <li>✅ Dual Camera</li>
                <li>✅ Fast Charging</li>
                <li>✅ 2 Year Warranty</li>
            </ul>
        </div>
    </div>

    <div class="payment-section">
        <h3>💳 Secure Payment</h3>
        <p>Complete your purchase securely with our integrated payment system.</p>
        
        <div class="payment-container">
            <div id="wio-payment-form">
                Payment form will load here...
            </div>
            
            <button id="load-payment" class="btn">Load Payment Form</button>
            <button id="process-payment" class="btn" style="display:none;">Process Payment</button>
        </div>
    </div>

    <script>
        ' . $paymentScript . '

        // Your existing page JavaScript
        let paymentForm = null;
        
        // Wait for WioPayments to be ready
        window.wioPaymentsReady = function() {
            console.log("WioPayments SDK is ready!");
            document.getElementById("load-payment").disabled = false;
        };

        // Load payment form when button is clicked
        document.getElementById("load-payment").addEventListener("click", function() {
            this.classList.add("loading");
            this.textContent = "Loading...";
            
            // Create payment form in existing element
            paymentForm = window.WioPayments.createPaymentForm("wio-payment-form", {
                cardStyle: {
                    base: {
                        fontSize: "16px",
                        color: "#2c3e50",
                        fontFamily: "Arial, sans-serif"
                    }
                }
            });
            
            document.getElementById("wio-payment-form").innerHTML = 
                "<div id=\"wio-card-element\" style=\"margin-bottom: 15px;\"></div>" +
                "<div id=\"wio-card-errors\" style=\"color: #e74c3c; margin-bottom: 15px;\"></div>";
            
            paymentForm.element.mount("#wio-card-element");
            
            this.style.display = "none";
            document.getElementById("process-payment").style.display = "inline-block";
            
            console.log("Payment form loaded successfully!");
        });

        // Process payment when button is clicked
        document.getElementById("process-payment").addEventListener("click", async function() {
            if (!paymentForm) {
                alert("Please load the payment form first");
                return;
            }
            
            this.classList.add("loading");
            this.textContent = "Processing...";
            
            try {
                // Confirm payment with Stripe
                const result = await paymentForm.confirmPayment("' . $payment->client_secret . '");
                
                if (result.error) {
                    document.getElementById("wio-card-errors").textContent = result.error.message;
                    this.classList.remove("loading");
                    this.textContent = "Process Payment";
                } else {
                    // Payment successful
                    alert("Payment successful! Redirecting...");
                    window.location.href = "/payment/success?payment_id=' . $payment->id . '";
                }
            } catch (error) {
                console.error("Payment error:", error);
                alert("Payment failed: " + error.message);
                this.classList.remove("loading");
                this.textContent = "Process Payment";
            }
        });
        
        // Your existing page functions
        function addToCart() {
            alert("Added to cart! Proceed to payment below.");
        }
        
        function showProductDetails() {
            alert("Product details displayed");
        }
    </script>
</body>
</html>';

    // Save the example page
    file_put_contents('existing-page-integration.html', $existingPageHtml);
    
    echo "✅ Payment created for existing page integration!\n";
    echo "Payment ID: " . $payment->id . "\n";
    echo "Amount: $" . number_format($payment->amount, 2) . "\n";
    echo "📄 Example page saved to: existing-page-integration.html\n";
    echo "🌐 Open the file in your browser to test the integration\n";

} catch (WioPaymentsException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Unexpected error: " . $e->getMessage() . "\n";
}

echo "\n💡 Integration Steps:\n";
echo "1. Include WioPayments.php in your existing PHP files\n";
echo "2. Create payment and get the JavaScript snippet\n";
echo "3. Add the script to your existing HTML pages\n";
echo "4. Use WioPayments.createPaymentForm() to add payment forms anywhere\n";
echo "5. Handle payment confirmation with your existing JavaScript\n";