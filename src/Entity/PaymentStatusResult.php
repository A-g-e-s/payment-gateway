<?php declare(strict_types=1);

namespace Ages\PaymentGateway\Entity;

use Ages\PaymentGateway\Entity\Enum\PaymentStatus;

readonly class PaymentStatusResult
{

    private function __construct(
        public bool $ok,
        public PaymentStatus|null $status,
        public string|null $transactionId,
        public string|null $refId,
        public int|null $amountInCents,
        public string|null $currency,
        public string|null $errorCode,
        public string|null $errorMessage,
    ) {
    }

    public static function success(
        PaymentStatus $status,
        string $transactionId,
        string $refId,
        int $amountInCents,
        string $currency,
    ): self {
        return new self(
            ok:            true,
            status:        $status,
            transactionId: $transactionId,
            refId:         $refId,
            amountInCents: $amountInCents,
            currency:      $currency,
            errorCode:     null,
            errorMessage:  null,
        );
    }

    public static function failure(string $errorCode, string|null $errorMessage): self
    {
        return new self(
            ok:            false,
            status:        null,
            transactionId: null,
            refId:         null,
            amountInCents: null,
            currency:      null,
            errorCode:     $errorCode,
            errorMessage:  $errorMessage,
        );
    }

}
