<?php declare(strict_types=1);

namespace Ages\PaymentGateway\Config;

readonly class ComgateConfig
{

    public const string GATEWAY_PRODUCTION = 'https://payments.comgate.cz/v1.0/';

    public function __construct(
        private string $merchant,
        private string $secret,
        private bool   $test = false,
        private string $gatewayUrl = self::GATEWAY_PRODUCTION,
    ) {
    }

    public function getMerchant(): string
    {
        return $this->merchant;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function isTest(): bool
    {
        return $this->test;
    }

    public function getGatewayUrl(): string
    {
        return $this->gatewayUrl;
    }

}
