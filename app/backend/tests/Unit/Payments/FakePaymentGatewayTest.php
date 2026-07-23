<?php

declare(strict_types=1);

namespace Tests\Unit\Payments;

use App\Enums\TransactionStatus;
use App\Payments\Gateways\FakePaymentGateway;
use App\Payments\PaymentIntent;
use PHPUnit\Framework\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    private function intent(array $options = []): PaymentIntent
    {
        return new PaymentIntent(orderId: 1, amount: 100.0, currency: 'COP', options: $options);
    }

    public function test_approves_by_default(): void
    {
        $result = (new FakePaymentGateway)->authorize($this->intent());

        $this->assertTrue($result->isApproved());
        $this->assertSame(TransactionStatus::Approved, $result->status);
        $this->assertStringStartsWith('fake_', $result->externalId);
        $this->assertNull($result->failureReason);
    }

    public function test_rejects_deterministically_with_simulate_flag(): void
    {
        $result = (new FakePaymentGateway)->authorize(
            $this->intent([FakePaymentGateway::SIMULATE_OPTION => FakePaymentGateway::SIMULATE_REJECT])
        );

        $this->assertFalse($result->isApproved());
        $this->assertSame(TransactionStatus::Rejected, $result->status);
        $this->assertNotNull($result->failureReason);
    }

    public function test_unknown_simulate_values_do_not_trigger_rejection(): void
    {
        $result = (new FakePaymentGateway)->authorize(
            $this->intent([FakePaymentGateway::SIMULATE_OPTION => 'anything-else'])
        );

        $this->assertTrue($result->isApproved());
    }
}
