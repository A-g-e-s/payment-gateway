<?php declare(strict_types=1);

namespace Ages\PaymentGateway\Gateway;

use Ages\PaymentGateway\Entity\CreatePayment;
use Ages\PaymentGateway\Entity\Enum\PaymentStatus;
use Ages\PaymentGateway\Entity\PaymentResult;
use Ages\PaymentGateway\Entity\PaymentStatusResult;
use Ages\PaymentGateway\Entity\RefundResult;
use Ages\PaymentGateway\Entity\StornoResult;
use Brick\Money\Money;
use Contributte\Comgate\Entity\Payment;
use Contributte\Comgate\Entity\PaymentStatus as ComgatePaymentStatus;
use Contributte\Comgate\Entity\Refund;
use Contributte\Comgate\Entity\Storno;
use Contributte\Comgate\Gateway\PaymentService;

class ComgateGateway implements GatewayInterface
{

    public function __construct(private readonly PaymentService $service)
    {
    }

    public function createPayment(CreatePayment $payment): PaymentResult
    {
        $comgatePayment = Payment::of(
            money:    $payment->getMoney(),
            label:    $payment->getLabel(),
            refId:    $payment->getRefId(),
            email:    $payment->getEmail(),
            fullName: $payment->getFullName(),
        );

        $res = $this->service->create($comgatePayment);

        if (!$res->isOk()) {
            return PaymentResult::failure(
                errorCode:    (string) $res->getErrorCode(),
                errorMessage: $res->getErrorMessage(),
            );
        }

        return PaymentResult::success(
            transactionId: $res->getTransId(),
            redirectUrl:   $res->getRedirect() ?? '',
        );
    }

    public function paymentStatus(string $transactionId): PaymentStatusResult
    {
        $res = $this->service->status(ComgatePaymentStatus::of($transactionId));

        if (!$res->isOk()) {
            return PaymentStatusResult::failure(
                errorCode:    (string) $res->getErrorCode(),
                errorMessage: $res->getErrorMessage(),
            );
        }

        return PaymentStatusResult::success(
            status:        PaymentStatus::fromComgate($res->getStatus()),
            transactionId: $res->getTransId(),
            refId:         $res->getRefId(),
            amountInCents: $res->getPrice(),
            currency:      $res->getCurr(),
        );
    }

    public function refundPayment(string $transactionId, Money $amount): RefundResult
    {
        $res = $this->service->refund(Refund::of(
            money:   $amount,
            transId: $transactionId,
        ));

        if (!$res->isOk()) {
            return RefundResult::failure(
                errorCode:    (string) $res->getErrorCode(),
                errorMessage: $res->getErrorMessage(),
            );
        }

        return RefundResult::success();
    }

    public function stornoPayment(string $transactionId): StornoResult
    {
        $res = $this->service->storno(Storno::of($transactionId));

        if (!$res->isOk()) {
            return StornoResult::failure(
                errorCode:    (string) $res->getErrorCode(),
                errorMessage: $res->getErrorMessage(),
            );
        }

        return StornoResult::success();
    }

}
