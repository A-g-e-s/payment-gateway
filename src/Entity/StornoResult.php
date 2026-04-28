<?php declare(strict_types=1);

namespace Ages\PaymentGateway\Entity;

readonly class StornoResult
{

    private function __construct(
        public bool $ok,
        public bool $gatewayNotified,
        public string|null $errorCode,
        public string|null $errorMessage,
    ) {
    }

    public static function success(): self
    {
        return new self(ok: true, gatewayNotified: true, errorCode: null, errorMessage: null);
    }

    /**
     * Gateway was not notified (e.g. GoPay has no cancel endpoint).
     * The payment link remains active until it expires — guard against
     * incoming payment notifications for cancelled orders.
     */
    public static function successNoop(): self
    {
        return new self(ok: true, gatewayNotified: false, errorCode: null, errorMessage: null);
    }

    public static function failure(string $errorCode, string|null $errorMessage): self
    {
        return new self(ok: false, gatewayNotified: false, errorCode: $errorCode, errorMessage: $errorMessage);
    }

}
