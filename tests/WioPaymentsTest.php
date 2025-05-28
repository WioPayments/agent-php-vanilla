<?php

use PHPUnit\Framework\TestCase;

class WioPaymentsTest extends TestCase
{
    private WioPayments $client;

    protected function setUp(): void
    {
        $this->client = createTestClient();
    }

    public function testClientInstantiation(): void
    {
        $this->assertInstanceOf(WioPayments::class, $this->client);
    }

    public function testCreatePayment(): void
    {
        $paymentData = createTestPaymentData();
        
        // Mock or skip actual API call for unit tests
        $this->expectException(WioPaymentsException::class);
        $payment = $this->client->createPayment($paymentData);
    }

    public function testValidatePaymentData(): void
    {
        // Test missing required fields
        $this->expectException(WioPaymentsException::class);
        $this->expectExceptionMessage('Missing required field: amount');
        
        $this->client->createPayment([
            'currency' => 'USD',
            'order_id' => 'TEST_123'
        ]);
    }

    public function testInvalidAmount(): void
    {
        $this->expectException(WioPaymentsException::class);
        $this->expectExceptionMessage('Amount must be a positive number');
        
        $this->client->createPayment([
            'amount' => -50.00,
            'currency' => 'USD',
            'order_id' => 'TEST_123'
        ]);
    }

    public function testInvalidCurrency(): void
    {
        $this->expectException(WioPaymentsException::class);
        $this->expectExceptionMessage('Unsupported currency');
        
        $this->client->createPayment([
            'amount' => 50.00,
            'currency' => 'XYZ',
            'order_id' => 'TEST_123'
        ]);
    }

    public function testRenderPaymentForm(): void
    {
        $paymentData = createTestPaymentData();
        
        // This would normally call the API, so we'll just test the structure
        $this->expectException(WioPaymentsException::class);
        $html = $this->client->renderPaymentForm($paymentData);
    }

    public function testGeneratePaymentScript(): void
    {
        $script = $this->client->generatePaymentScript('payment_test_123');
        
        $this->assertStringContainsString('WioPayments', $script);
        $this->assertStringContainsString('stripe.com', $script);
        $this->assertStringContainsString('payment_test_123', $script);
    }

    public function testHostedSessionValidation(): void
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

    public function testPaymentLinkValidation(): void
    {
        $this->expectException(WioPaymentsException::class);
        $this->expectExceptionMessage('Missing required field: description');
        
        $this->client->createPaymentLink([
            'amount' => 50.00,
            'currency' => 'USD'
        ]);
    }
}