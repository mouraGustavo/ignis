<?php

namespace Tests\Unit\Services\Payment;

use App\Enums\PaymentStatus;
use App\Exceptions\InvalidPaymentTransitionException;
use App\Services\Payment\PaymentStatusService;
use PHPUnit\Framework\Attributes\Test;
use Tests\UnitTestCase;

class PaymentStatusServiceTest extends UnitTestCase
{
    private PaymentStatusService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PaymentStatusService;
    }

    #[Test]
    public function it_allows_pending_to_paid_transition(): void
    {
        $this->assertTrue(
            $this->service->canTransition(PaymentStatus::PENDING, PaymentStatus::PAID)
        );
    }

    #[Test]
    public function it_blocks_paid_to_failed_transition(): void
    {
        $this->assertFalse(
            $this->service->canTransition(PaymentStatus::PAID, PaymentStatus::FAILED)
        );
    }

    #[Test]
    public function it_throws_on_invalid_transition(): void
    {
        $this->expectException(InvalidPaymentTransitionException::class);

        $this->service->assertCanTransition(PaymentStatus::PAID, PaymentStatus::FAILED);
    }
}
