<?php declare(strict_types=1);

namespace Ages\PaymentGateway\Gateway;

use Ages\PaymentGateway\Entity\CreatePayment;
use Ages\PaymentGateway\Entity\PaymentResult;
use Ages\PaymentGateway\Entity\PaymentStatusResult;
use Ages\PaymentGateway\Entity\RefundResult;
use Ages\PaymentGateway\Entity\StornoResult;
use Brick\Money\Money;

interface GatewayInterface
{

    public function createPayment(CreatePayment $payment): PaymentResult;

    public function paymentStatus(string $transactionId): PaymentStatusResult;

    public function refundPayment(string $transactionId, Money $amount): RefundResult;

    public function stornoPayment(string $transactionId): StornoResult;

}
