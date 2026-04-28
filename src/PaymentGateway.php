<?php declare(strict_types=1);

namespace Ages\PaymentGateway;

use Ages\PaymentGateway\Config\ComgateConfig;
use Ages\PaymentGateway\Config\GoPayConfig;
use Ages\PaymentGateway\Gateway\ComgateGateway;
use Ages\PaymentGateway\Gateway\GoPayGateway;
use Contributte\Comgate\Comgate;
use Contributte\Comgate\Gateway\PaymentService as ComgatePaymentService;
use Contributte\Comgate\Http\HttpClient as ComgateHttpClient;
use Ages\PaymentGateway\Exception\GatewayInitializationException;
use GoPay\Api;
use GoPay\Payments as GoPayPayments;
use GuzzleHttp\Client as GuzzleClient;

readonly class PaymentGateway
{

    public ComgateGateway|null $comgate;
    public GoPayGateway|null $gopay;

    public function __construct(
        ComgateConfig|null $comgateConfig = null,
        GoPayConfig|null $goPayConfig = null,
    ) {
        $this->comgate = $comgateConfig !== null ? $this->buildComgate($comgateConfig) : null;
        $this->gopay   = $goPayConfig !== null   ? $this->buildGoPay($goPayConfig)   : null;
    }

    private function buildComgate(ComgateConfig $config): ComgateGateway
    {
        $comgate    = new Comgate($config->getMerchant(), $config->getSecret(), $config->isTest());
        $guzzle     = new GuzzleClient(['base_uri' => $config->getGatewayUrl()]);
        $httpClient = new ComgateHttpClient($guzzle, $comgate);

        return new ComgateGateway(new ComgatePaymentService($httpClient));
    }

    private function buildGoPay(GoPayConfig $config): GoPayGateway
    {
        $payments = Api::payments($config->toGoPayArray());
        if (!$payments instanceof GoPayPayments) {
            throw new GatewayInitializationException('Failed to initialize GoPay payments');
        }
        return new GoPayGateway($payments, $config);
    }

}
