<?php declare(strict_types=1);

namespace Ages\PaymentGateway\Entity;

readonly class RefundResult
{

    private function __construct(
        public bool $ok,
        public string|null $errorCode,
        public string|null $errorMessage,
    ) {
    }

    public static function success(): self
    {
        return new self(ok: true, errorCode: null, errorMessage: null);
    }

    public static function failure(string $errorCode, string|null $errorMessage): self
    {
        return new self(ok: false, errorCode: $errorCode, errorMessage: $errorMessage);
    }

}
