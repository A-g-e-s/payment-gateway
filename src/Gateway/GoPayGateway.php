<?php declare(strict_types=1);

namespace Ages\PaymentGateway\Gateway;

use Ages\PaymentGateway\Config\GoPayConfig;
use Ages\PaymentGateway\Entity\CreatePayment;
use Ages\PaymentGateway\Entity\Enum\PaymentStatus;
use Ages\PaymentGateway\Entity\PaymentResult;
use Ages\PaymentGateway\Entity\PaymentStatusResult;
use Ages\PaymentGateway\Entity\RefundResult;
use Ages\PaymentGateway\Entity\StornoResult;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Ages\PaymentGateway\Exception\InvalidGatewayResponseException;
use GoPay\Http\Response;
use GoPay\Payments;

readonly class GoPayGateway implements GatewayInterface
{

    public function __construct(
        private Payments    $payments,
        private GoPayConfig $config,
    ) {
    }

    public function createPayment(CreatePayment $payment): PaymentResult
    {
        $amountInCents = $payment->getMoney()
            ->multipliedBy(100, RoundingMode::UNNECESSARY)
            ->getAmount()
            ->toInt();

        $res = $this->call($this->payments->createPayment([
            'payer'             => [
                'contact' => [
                    'first_name' => $payment->getFullName(),
                    'email'      => $payment->getEmail(),
                ],
            ],
            'amount'            => $amountInCents,
            'currency'          => $payment->getMoney()->getCurrency()->getCurrencyCode(),
            'order_number'      => $payment->getRefId(),
            'order_description' => $payment->getLabel(),
            'items'             => [
                ['name' => $payment->getLabel(), 'amount' => $amountInCents],
            ],
            'callback'          => [
                'return_url'       => $this->config->getReturnUrl(),
                'notification_url' => $this->config->getNotificationUrl(),
            ],
        ]));

        if (!$res->hasSucceed()) {
            return PaymentResult::failure(
                errorCode:    (string) $res->statusCode,
                errorMessage: (string) $res,
            );
        }

        return PaymentResult::success(
            transactionId: $this->requireString($res->json, 'id'),
            redirectUrl:   $this->requireString($res->json, 'gw_url'),
        );
    }

    public function paymentStatus(string $transactionId): PaymentStatusResult
    {
        $res = $this->call($this->payments->getStatus($transactionId));

        if (!$res->hasSucceed()) {
            return PaymentStatusResult::failure(
                errorCode:    (string) $res->statusCode,
                errorMessage: (string) $res,
            );
        }

        return PaymentStatusResult::success(
            status:        PaymentStatus::fromGoPay($this->requireString($res->json, 'state')),
            transactionId: $this->requireString($res->json, 'id'),
            refId:         $this->requireString($res->json, 'order_number'),
            amountInCents: $this->requireInt($res->json, 'amount'),
            currency:      $this->requireString($res->json, 'currency'),
        );
    }

    public function refundPayment(string $transactionId, Money $amount): RefundResult
    {
        $amountInCents = $amount
            ->multipliedBy(100, RoundingMode::UNNECESSARY)
            ->getAmount()
            ->toInt();

        $res = $this->call($this->payments->refundPayment($transactionId, $amountInCents));

        if (!$res->hasSucceed()) {
            return RefundResult::failure(
                errorCode:    (string) $res->statusCode,
                errorMessage: (string) $res,
            );
        }

        return RefundResult::success();
    }

    public function stornoPayment(string $transactionId): StornoResult
    {
        return StornoResult::successNoop();
    }

    private function call(mixed $res): Response
    {
        if (!$res instanceof Response) {
            throw new InvalidGatewayResponseException('Unexpected GoPay response type');
        }
        return $res;
    }

    /** @param array<mixed> $json */
    private function requireString(array $json, string $key): string
    {
        $value = $json[$key] ?? null;
        if (is_string($value)) {
            return $value;
        }
        if (is_int($value)) {
            return (string) $value;
        }
        throw new InvalidGatewayResponseException(sprintf("GoPay response: '%s' missing or not a string", $key));
    }

    /** @param array<mixed> $json */
    private function requireInt(array $json, string $key): int
    {
        $value = $json[$key] ?? null;
        if (is_int($value)) {
            return $value;
        }
        throw new InvalidGatewayResponseException(sprintf("GoPay response: '%s' missing or not an int", $key));
    }

}
