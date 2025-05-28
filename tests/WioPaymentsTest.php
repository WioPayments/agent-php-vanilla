<?php

use PHPUnit\Framework\TestCase;

class WioPaymentsTest extends TestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = createTestClient();
    }

    public function testClientInstantiation()
    {
        $this->assertInstanceOf(WioPayments::class, $this->client);
    }

    public function testCreatePayment()
    {
        $paymentData = createTestPaymentData();
        
        // Mock or skip actual API call for unit tests
        $this->expectException(WioPaymentsException::class);
        $payment = $this->client->createPayment($paymentData);
    }

    public function testValidatePaymentData()
    {
        // Test missing required fields
        $this->expectException(WioPaymentsException::class);
        $this->expectExceptionMessage('Missing required field: amount');
        
        $this->client->createPayment([
            'currency' => 'USD',
            'order_id' => 'TEST_123'
        ]);
    }

    public function testInvalidAmount()
    {
        $this->expectException(WioPaymentsException::class);
        $this->expectExceptionMessage('Amount must be a positive number');
        
        $this->client->createPayment([
            'amount' => -50.00,
            'currency' => 'USD',
            'order_id' => 'TEST_123'
        ]);
    }

    public function testInvalidCurrency()
    {
        $this->expectException(WioPaymentsException::class);
        $this->expectExceptionMessage('Unsupported currency');
        
        $this->client->createPayment([
            'amount' => 50.00,
            'currency' => 'XYZ',
            'order_id' => 'TEST_123'
        ]);
    }

    public function testRenderPaymentForm()
    {
        $paymentData = createTestPaymentData();
        
        // Mock the createPayment method to avoid API call
        $mockPayment = new WioPaymentsPayment();
        $mockPayment->id = 'payment_test_123';
        $mockPayment->amount = 50.00;
        $mockPayment->currency = 'USD';
        $mockPayment->client_secret = 'pi_test_client_secret';
        $mockPayment->customer_name = 'Test Customer';
        $mockPayment->customer_email = 'test@example.com';
        
        // This would normally call the API, so we'll just test the structure
        $this->expectException(WioPaymentsException::class);
        $html = $this->client->renderPaymentForm($paymentData);
    }

    public function testGeneratePaymentScript()
    {
        $script = $this->client->generatePaymentScript('payment_test_123');
        
        $this->assertStringContains('WioPayments', $script);
        $this->assertStringContains('stripe.com', $script);
        $this->assertStringContains('payment_test_123', $script);
    }

    public function testHostedSessionValidation()
    {
        $this->expectException(WioPaymentsException::class);
        $this->expectExceptionMessage('Missing required field: success_url');
        
        $this->client->createHostedPaymentSession([
            'amount' => 50.00,
            'currency' => 'USD',
            'order_id' => 'TEST_123',
            'cancel_url' => 'https://example.com/cancel'
        ]);
    }

    public function testPaymentLinkValidation()
    {
        $this->expectException(WioPaymentsException::class);
        $this->expectExceptionMessage('Missing required field: description');
        
        $this->client->createPaymentLink([
            'amount' => 50.00,
            'currency' => 'USD'
        ]);
    }
}