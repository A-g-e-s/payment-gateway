<?php declare(strict_types=1);

namespace Ages\PaymentGateway\Config;

use GoPay\Definition\Language;
use GoPay\Definition\TokenScope;

class GoPayConfig
{

    public const string GATEWAY_PRODUCTION = 'https://gate.gopay.cz/';
    public const string GATEWAY_SANDBOX = 'https://gw.sandbox.gopay.com/';

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $goid,
        private readonly string $returnUrl,
        private readonly string $notificationUrl,
        private readonly bool   $test = false,
        private readonly string $scope = TokenScope::ALL,
        private readonly string $language = Language::CZECH,
        private readonly int    $timeout = 30,
    )
    {
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function getGoid(): string
    {
        return $this->goid;
    }

    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }

    public function getNotificationUrl(): string
    {
        return $this->notificationUrl;
    }

    public function isTest(): bool
    {
        return $this->test;
    }

    public function getGatewayUrl(): string
    {
        return $this->test ? self::GATEWAY_SANDBOX : self::GATEWAY_PRODUCTION;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     *
     * @return array{
     *     clientId: string,
     *     clientSecret: string,
     *     goid: string,
     *     gatewayUrl: string,
     *     scope: string,
     *     language: string,
     *     timeout: int
     * }
     */
    public function toGoPayArray(): array
    {
        return [
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'goid' => $this->goid,
            'gatewayUrl' => $this->getGatewayUrl(),
            'scope' => $this->scope,
            'language' => $this->language,
            'timeout' => $this->timeout,
        ];
    }

}
