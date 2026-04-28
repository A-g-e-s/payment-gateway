<?php declare(strict_types=1);

namespace Ages\PaymentGateway\Entity;

use Brick\Money\Money;

class CreatePayment
{

    private function __construct(
        private readonly Money $money,
        private readonly string $label,
        private readonly string $refId,
        private readonly string $email,
        private readonly string $fullName,
    ) {
    }

    public static function of(
        Money $money,
        string $label,
        string $refId,
        string $email,
        string $fullName,
    ): self {
        return new self($money, $label, $refId, $email, $fullName);
    }

    public function getMoney(): Money
    {
        return $this->money;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getRefId(): string
    {
        return $this->refId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

}
