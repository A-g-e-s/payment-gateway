<?php declare(strict_types=1);

namespace Ages\PaymentGateway\Entity;

readonly class PaymentResult
{

    private function __construct(
        public bool $ok,
        public string|null $transactionId,
        public string|null $redirectUrl,
        public string|null $errorCode,
        public string|null $errorMessage,
    ) {
    }

    public static function success(string $transactionId, string $redirectUrl): self
    {
        return new self(
            ok:            true,
            transactionId: $transactionId,
            redirectUrl:   $redirectUrl,
            errorCode:     null,
            errorMessage:  null,
        );
    }

    public static function failure(string $errorCode, string|null $errorMessage): self
    {
        return new self(
            ok:            false,
            transactionId: null,
            redirectUrl:   null,
            errorCode:     $errorCode,
            errorMessage:  $errorMessage,
        );
    }

}
