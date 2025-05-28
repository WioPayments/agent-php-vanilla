<?php

/**
 * WioPayments Gateway PHP SDK - Vanilla PHP Version
 * 
 * A standalone PHP library for integrating with WioPayments Gateway
 * No dependencies required - works with any PHP project
 * 
 * @version 1.0.0
 * @author WioPayments Team
 */

if (!class_exists('WioPaymentsException')) {
    /**
     * WioPayments Exception class
     */
    class WioPaymentsException extends Exception
    {
        public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
        }
    }
}

if (!class_exists('WioPaymentsHttpClient')) {
    /**
     * Simple HTTP client using cURL for API communication
     */
    class WioPaymentsHttpClient
    {
        private string $baseUrl;
        private int $timeout;
        private bool $verifySsl;

        public function __construct(array $config)
        {
            $this->baseUrl = rtrim($config['base_url'], '/');
            $this->timeout = $config['timeout'] ?? 30;
            $this->verifySsl = $config['verify_ssl'] ?? true;
        }

        /**
         * Make GET request
         */
        public function get(string $endpoint, array $options = []): array
        {
            return $this->request('GET', $endpoint, $options);
        }

        /**
         * Make POST request
         */
        public function post(string $endpoint, array $options = []): array
        {
            return $this->request('POST', $endpoint, $options);
        }

        /**
         * Make PUT request
         */
        public function put(string $endpoint, array $options = []): array
        {
            return $this->request('PUT', $endpoint, $options);
        }

        /**
         * Make DELETE request
         */
        public function delete(string $endpoint, array $options = []): array
        {
            return $this->request('DELETE', $endpoint, $options);
        }

        /**
         * Execute HTTP request using cURL
         */
        private function request(string $method, string $endpoint, array $options = []): array
        {
            $url = $this->baseUrl . $endpoint;
            $ch = curl_init();

            // Basic cURL options
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_SSL_VERIFYPEER => $this->verifySsl,
                CURLOPT_SSL_VERIFYHOST => $this->verifySsl ? 2 : 0,
                CURLOPT_USERAGENT => 'WioPayments-PHP-SDK-Vanilla/1.0.0',
                CURLOPT_CUSTOMREQUEST => $method,
            ]);

            // Set headers
            $headers = ['Accept: application/json'];
            if (isset($options['headers'])) {
                foreach ($options['headers'] as $key => $value) {
                    $headers[] = $key . ': ' . $value;
                }
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            // Set JSON data for POST/PUT requests
            if (isset($options['json']) && in_array($method, ['POST', 'PUT'])) {
                $jsonData = json_encode($options['json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                $headers[] = 'Content-Type: application/json';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($response === false) {
                throw new WioPaymentsException('cURL Error: ' . $error);
            }

            return $this->handleResponse($response, $httpCode);
        }

        /**
         * Handle HTTP response
         */
        private function handleResponse(string $response, int $httpCode): array
        {
            if ($httpCode < 200 || $httpCode >= 300) {
                throw new WioPaymentsException("HTTP {$httpCode}: {$response}", $httpCode);
            }

            $data = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new WioPaymentsException('Invalid JSON response: ' . json_last_error_msg());
            }

            // Check for API errors in response
            if (isset($data['error'])) {
                throw new WioPaymentsException($data['error']['message'] ?? 'API Error', $data['error']['code'] ?? 400);
            }

            return $data;
        }
    }
}

if (!class_exists('WioPaymentsPayment')) {
    /**
     * Payment model class
     */
    class WioPaymentsPayment
    {
        public ?string $id;
        public ?float $amount;
        public ?string $currency;
        public ?string $status;
        public ?string $order_id;
        public ?string $client_secret;
        public ?string $customer_name;
        public ?string $customer_email;
        public ?string $created_at;

        public static function fromArray(array $data): self
        {
            $payment = new self();
            $payment->id = $data['id'] ?? null;
            $payment->amount = $data['amount'] ?? null;
            $payment->currency = $data['currency'] ?? null;
            $payment->status = $data['status'] ?? null;
            $payment->order_id = $data['order_id'] ?? null;
            $payment->client_secret = $data['client_secret'] ?? null;
            $payment->customer_name = $data['customer']['name'] ?? null;
            $payment->customer_email = $data['customer']['email'] ?? null;
            $payment->created_at = $data['created_at'] ?? null;

            return $payment;
        }
    }
}

if (!class_exists('WioPaymentsPaymentStatus')) {
    /**
     * Payment Status model class
     */
    class WioPaymentsPaymentStatus
    {
        public ?string $id;
        public ?string $status;
        public ?float $amount;
        public ?string $currency;
        public ?string $order_id;
        public ?string $updated_at;

        public static function fromArray(array $data): self
        {
            $status = new self();
            $status->id = $data['id'] ?? null;
            $status->status = $data['status'] ?? null;
            $status->amount = $data['amount'] ?? null;
            $status->currency = $data['currency'] ?? null;
            $status->order_id = $data['order_id'] ?? null;
            $status->updated_at = $data['updated_at'] ?? null;

            return $status;
        }
    }
}

/**
 * WioPayments Gateway PHP SDK Client - Vanilla PHP Version
 * 
 * Comprehensive SDK for integrating with WioPayments Gateway
 * Includes automatic Stripe JS integration for seamless payments
 * No external dependencies - works with any PHP project
 */
class WioPayments
{
    private string $apiKey;
    private string $baseUrl;
    private WioPaymentsHttpClient $httpClient;
    private array $config;

    /**
     * Initialize WioPayments client
     *
     * @param string $apiKey Site API key from WioPayments dashboard
     * @param array $config Additional configuration options
     */
    public function __construct(string $apiKey, array $config = [])
    {
        $this->apiKey = $apiKey;
        $this->config = array_merge([
            'base_url' => 'https://gw.wiopayments.com',
            'timeout' => 30,
            'verify_ssl' => true,
            'auto_include_stripe_js' => true,
        ], $config);
        
        $this->baseUrl = rtrim($this->config['base_url'], '/');
        $this->httpClient = new WioPaymentsHttpClient($this->config);
    }

    /**
     * Create a new payment
     *
     * @param array $paymentData Payment information
     * @return WioPaymentsPayment
     * @throws WioPaymentsException
     */
    public function createPayment(array $paymentData): WioPaymentsPayment
    {
        $this->validatePaymentData($paymentData);

        $response = $this->httpClient->post('/api/v1/create-payment', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => $paymentData
        ]);

        return WioPaymentsPayment::fromArray($response);
    }

    /**
     * Get payment status
     *
     * @param string $paymentId
     * @return WioPaymentsPaymentStatus
     * @throws WioPaymentsException
     */
    public function getPaymentStatus(string $paymentId): WioPaymentsPaymentStatus
    {
        $response = $this->httpClient->get("/api/v1/payment/{$paymentId}/status", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ]
        ]);

        return WioPaymentsPaymentStatus::fromArray($response);
    }

    /**
     * Generate Stripe JS payment form HTML
     *
     * @param array $paymentData Payment information
     * @param array $options Form customization options
     * @return string HTML form with integrated Stripe JS
     * @throws WioPaymentsException
     */
    public function renderPaymentForm(array $paymentData, array $options = []): string
    {
        // Create payment intent first
        $payment = $this->createPayment($paymentData);

        $formOptions = array_merge([
            'submit_button_text' => 'Pay Now',
            'loading_button_text' => 'Processing...',
            'success_url' => '/payment/success',
            'cancel_url' => '/payment/cancel',
            'custom_css' => '',
            'form_id' => 'wiopayments-form',
            'card_element_id' => 'wiopayments-card-element',
            'error_element_id' => 'wiopayments-card-errors',
        ], $options);

        return $this->generatePaymentFormHtml($payment, $formOptions);
    }

    /**
     * Generate payment form HTML with Stripe JS integration
     */
    private function generatePaymentFormHtml(WioPaymentsPayment $payment, array $options): string
    {
        $stripePublishableKey = $this->getStripePublishableKey();
        $amount = number_format($payment->amount, 2);
        $currency = strtoupper($payment->currency);

        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WioPayments - Secure Payment</title>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        .wiopayments-form {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;
        }
        
        .wiopayments-card-element {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin: 10px 0;
        }
        
        .wiopayments-button {
            background: #1a73e8;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }
        
        .wiopayments-button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .wiopayments-error {
            color: #e74c3c;
            margin-top: 10px;
        }
        
        .wiopayments-amount {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }
        
        ' . $options['custom_css'] . '
    </style>
</head>
<body>
    <div class="wiopayments-form" id="' . $options['form_id'] . '">
        <div class="wiopayments-amount">' . $amount . ' ' . $currency . '</div>
        
        <form id="payment-form">
            <div id="' . $options['card_element_id'] . '" class="wiopayments-card-element">
                <!-- Stripe Elements will create form elements here -->
            </div>
            
            <div id="' . $options['error_element_id'] . '" class="wiopayments-error" role="alert"></div>
            
            <button type="submit" id="submit-button" class="wiopayments-button">
                <span id="button-text">' . $options['submit_button_text'] . '</span>
                <div id="spinner" style="display: none;">⏳</div>
            </button>
        </form>
    </div>

    <script>
        // Initialize Stripe
        const stripe = Stripe(\'' . $stripePublishableKey . '\');
        const elements = stripe.elements();

        // Create card element
        const cardElement = elements.create(\'card\', {
            style: {
                base: {
                    fontSize: \'16px\',
                    color: \'#424770\',
                    \'::placeholder\': {
                        color: \'#aab7c4\',
                    },
                },
            },
        });

        cardElement.mount(\'#' . $options['card_element_id'] . '\');

        // Handle form submission
        const form = document.getElementById(\'payment-form\');
        const submitButton = document.getElementById(\'submit-button\');
        const buttonText = document.getElementById(\'button-text\');
        const spinner = document.getElementById(\'spinner\');
        const errorElement = document.getElementById(\'' . $options['error_element_id'] . '\');

        form.addEventListener(\'submit\', async (event) => {
            event.preventDefault();
            
            submitButton.disabled = true;
            buttonText.textContent = \'' . $options['loading_button_text'] . '\';
            spinner.style.display = \'inline-block\';
            errorElement.textContent = \'\';

            const {error} = await stripe.confirmCardPayment(\'' . $payment->client_secret . '\', {
                payment_method: {
                    card: cardElement,
                    billing_details: {
                        name: \'' . addslashes($payment->customer_name) . '\',
                        email: \'' . addslashes($payment->customer_email) . '\'
                    }
                }
            });

            if (error) {
                errorElement.textContent = error.message;
                submitButton.disabled = false;
                buttonText.textContent = \'' . $options['submit_button_text'] . '\';
                spinner.style.display = \'none\';
            } else {
                // Payment successful
                window.location.href = \'' . $options['success_url'] . '?payment_id=' . $payment->id . '\';
            }
        });

        // Handle real-time validation errors from the card Element
        cardElement.on(\'change\', ({error}) => {
            if (error) {
                errorElement.textContent = error.message;
            } else {
                errorElement.textContent = \'\';
            }
        });
    </script>
</body>
</html>';
    }

    /**
     * Create hosted payment session
     *
     * @param array $sessionData Payment session information
     * @return array Session details with payment URL
     * @throws WioPaymentsException
     */
    public function createHostedPaymentSession(array $sessionData): array
    {
        $this->validateHostedSessionData($sessionData);

        $response = $this->httpClient->post('/api/v1/hosted/sessions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => $sessionData
        ]);

        return $response;
    }

    /**
     * Get hosted payment session status
     *
     * @param string $sessionId
     * @return array Session status information
     * @throws WioPaymentsException
     */
    public function getHostedSessionStatus(string $sessionId): array
    {
        $response = $this->httpClient->get("/api/v1/hosted/sessions/{$sessionId}/status", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ]
        ]);

        return $response;
    }

    /**
     * Generate redirect URL for hosted payment
     *
     * @param array $sessionData Payment session information
     * @return string Direct payment URL for redirection
     * @throws WioPaymentsException
     */
    public function createHostedPaymentUrl(array $sessionData): string
    {
        $session = $this->createHostedPaymentSession($sessionData);
        return $session['payment_url'];
    }

    /**
     * Create a payment link for one-time or recurring payments
     *
     * @param array $linkData Payment link configuration
     * @return array Payment link details including URL and metadata
     * @throws WioPaymentsException
     */
    public function createPaymentLink(array $linkData): array
    {
        $this->validatePaymentLinkData($linkData);

        $response = $this->httpClient->post('/api/v1/payment-links', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => $linkData
        ]);

        return $response;
    }

    /**
     * Get payment link details and status
     *
     * @param string $linkId Payment link identifier
     * @return array Payment link information
     * @throws WioPaymentsException
     */
    public function getPaymentLink(string $linkId): array
    {
        $response = $this->httpClient->get("/api/v1/payment-links/{$linkId}", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ]
        ]);

        return $response;
    }

    /**
     * List all payment links for the site
     *
     * @param array $filters Optional filters (status, created_after, etc.)
     * @return array List of payment links
     * @throws WioPaymentsException
     */
    public function listPaymentLinks(array $filters = []): array
    {
        $queryParams = http_build_query($filters);
        $url = '/api/v1/payment-links' . ($queryParams ? '?' . $queryParams : '');

        $response = $this->httpClient->get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ]
        ]);

        return $response;
    }

    /**
     * Generate JavaScript snippet for existing pages
     *
     * @param string $paymentId
     * @return string JavaScript code to include in existing pages
     */
    public function generatePaymentScript(string $paymentId): string
    {
        $stripePublishableKey = $this->getStripePublishableKey();
        
        return '// WioPayments Stripe JS Integration
(function() {
    const script = document.createElement(\'script\');
    script.src = \'https://js.stripe.com/v3/\';
    script.onload = function() {
        window.WioPayments = {
            stripe: Stripe(\'' . $stripePublishableKey . '\'),
            paymentId: \'' . $paymentId . '\',
            
            // Create payment form on existing element
            createPaymentForm: function(elementId, options = {}) {
                const element = document.getElementById(elementId);
                if (!element) {
                    console.error(\'WioPayments: Element not found:\', elementId);
                    return;
                }
                
                const elements = this.stripe.elements();
                const cardElement = elements.create(\'card\', options.cardStyle || {});
                
                element.innerHTML = \'<div id="wio-card-element"></div><div id="wio-card-errors"></div>\';
                cardElement.mount(\'#wio-card-element\');
                
                return {
                    element: cardElement,
                    confirmPayment: async function(clientSecret) {
                        return await window.WioPayments.stripe.confirmCardPayment(clientSecret, {
                            payment_method: { card: cardElement }
                        });
                    }
                };
            }
        };
        
        // Trigger ready event
        if (typeof window.wioPaymentsReady === \'function\') {
            window.wioPaymentsReady();
        }
    };
    document.head.appendChild(script);
})();';
    }

    /**
     * Get Stripe publishable key from WioPayments API
     */
    private function getStripePublishableKey(): string
    {
        // In production, this would fetch the publishable key from WioPayments API
        // For now, return the test key
        return 'pk_test_BmElE3Cz2trZCkyckCQSKVIF';
    }

    /**
     * Validate payment data
     */
    private function validatePaymentData(array $data): void
    {
        $required = ['amount', 'currency', 'order_id'];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new WioPaymentsException("Missing required field: {$field}");
            }
        }

        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new WioPaymentsException('Amount must be a positive number');
        }

        if (!in_array(strtoupper($data['currency']), ['USD', 'EUR', 'TRY', 'GBP'])) {
            throw new WioPaymentsException('Unsupported currency');
        }
    }

    /**
     * Validate hosted session data
     */
    private function validateHostedSessionData(array $data): void
    {
        $required = ['amount', 'currency', 'order_id', 'success_url', 'cancel_url'];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new WioPaymentsException("Missing required field: {$field}");
            }
        }

        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new WioPaymentsException('Amount must be a positive number');
        }

        if (!in_array(strtoupper($data['currency']), ['USD', 'EUR', 'TRY', 'GBP'])) {
            throw new WioPaymentsException('Unsupported currency');
        }

        if (!filter_var($data['success_url'], FILTER_VALIDATE_URL)) {
            throw new WioPaymentsException('Invalid success URL');
        }

        if (!filter_var($data['cancel_url'], FILTER_VALIDATE_URL)) {
            throw new WioPaymentsException('Invalid cancel URL');
        }
    }

    /**
     * Validate payment link data
     */
    private function validatePaymentLinkData(array $data): void
    {
        $required = ['amount', 'currency', 'description'];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new WioPaymentsException("Missing required field: {$field}");
            }
        }

        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new WioPaymentsException('Amount must be a positive number');
        }

        if (!in_array(strtoupper($data['currency']), ['USD', 'EUR', 'TRY', 'GBP'])) {
            throw new WioPaymentsException('Unsupported currency');
        }
    }
}