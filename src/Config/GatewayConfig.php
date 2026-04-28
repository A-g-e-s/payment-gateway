<?php declare(strict_types=1);

namespace Ages\PaymentGateway\Config;

readonly class GatewayConfig
{

    public function __construct(
        private ComgateConfig $comgate,
        private GoPayConfig   $gopay,
    ) {
    }

    public function getComgate(): ComgateConfig
    {
        return $this->comgate;
    }

    public function getGoPay(): GoPayConfig
    {
        return $this->gopay;
    }

}
