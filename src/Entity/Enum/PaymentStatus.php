<?php declare(strict_types=1);

namespace Ages\PaymentGateway\Entity\Enum;

enum PaymentStatus: string
{
    case Pending           = 'PENDING';
    case Paid              = 'PAID';
    case Authorized        = 'AUTHORIZED';
    case Cancelled         = 'CANCELLED';
    case Refunded          = 'REFUNDED';
    case PartiallyRefunded = 'PARTIALLY_REFUNDED';
    case TimedOut          = 'TIMED_OUT';
    case Unknown           = 'UNKNOWN';

    public static function fromComgate(string $status): self
    {
        return match ($status) {
            'PENDING'    => self::Pending,
            'PAID'       => self::Paid,
            'AUTHORIZED' => self::Authorized,
            'CANCELLED'  => self::Cancelled,
            default      => self::Unknown,
        };
    }

    public static function fromGoPay(string $status): self
    {
        return match ($status) {
            'CREATED', 'PAYMENT_METHOD_CHOSEN' => self::Pending,
            'PAID'                             => self::Paid,
            'AUTHORIZED'                       => self::Authorized,
            'CANCELED'                         => self::Cancelled,
            'TIMEOUTED'                        => self::TimedOut,
            'REFUNDED'                         => self::Refunded,
            'PARTIALLY_REFUNDED'               => self::PartiallyRefunded,
            default                            => self::Unknown,
        };
    }
}
