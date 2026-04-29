# ages/payment-gateway

Jednotné API pro platební brány **Comgate** a **GoPay**. Obě integrace sdílejí stejné entity a rozhraní — přepnutí brány nevyžaduje změnu aplikačního kódu.

## Požadavky

- PHP 8.4+
- `gopay/payments-sdk-php` ^1.11
- `contributte/comgate` ^0.5.1

## Konfigurace (Nette DI / NEON)

```neon
services:
    comgateConfig: Ages\PaymentGateway\Config\ComgateConfig(
        merchant: 'merchant-id'
        secret:   'merchant-secret'
        test:     false
        # gatewayUrl: https://payments.comgate.cz/v1.0/    # výchozí hodnota
    )

    goPayConfig: Ages\PaymentGateway\Config\GoPayConfig(
        clientId:        'my-client-id'
        clientSecret:    'my-client-secret'
        goid:            'goPay-id'
        returnUrl:       'https://shop.cz/payment/return'
        notificationUrl: 'https://shop.cz/payment/notify'
        test:            false
        # scope:    payment-all    # výchozí hodnota
        # language: CS             # výchozí hodnota
        # timeout:  30             # výchozí hodnota
    )

    - Ages\PaymentGateway\PaymentGateway
```

Obě konfigurace jsou **volitelné** — shop používající pouze Comgate vynechá `goPayConfig` a naopak. `PaymentGateway` autowire dle přítomných typů v kontejneru.

### Test / sandbox

| Brána   | `test: true`                            |
|---------|-----------------------------------------|
| Comgate | volání jdou na produkci, transakce jsou označeny jako testovací |
| GoPay   | přepne na `https://gw.sandbox.gopay.com/` |

---

## Použití

### Vytvoření platby

```php
use Ages\PaymentGateway\Entity\CreatePayment;
use Brick\Money\Money;

$payment = CreatePayment::of(
    money:    Money::of($order->priceTax, $order->currency->iso),
    label:    sprintf('Objednávka %s', $order->code),
    refId:    $order->code,
    email:    $order->email,
    fullName: $order->invoiceFullName,
);

// Comgate
$res = $this->paymentGateway->comgate->createPayment($payment);

// nebo GoPay
$res = $this->paymentGateway->gopay->createPayment($payment);

if (!$res->ok) {
    // $res->errorCode, $res->errorMessage
    return null;
}

$order->transactionId  = $res->transactionId;
$order->transactionUrl = $res->redirectUrl;   // URL pro přesměrování zákazníka
```

### Stav platby

```php
$res = $this->paymentGateway->comgate->paymentStatus($order->transactionId);

if (!$res->ok) {
    // chyba komunikace s bránou
}

echo $res->status->value;     // PAID, PENDING, CANCELLED, ...
echo $res->amountInCents;     // částka v haléřích
echo $res->currency;          // CZK
echo $res->refId;             // číslo objednávky
```

#### Stavy platby (`PaymentStatus` enum)

| Hodnota              | Comgate     | GoPay                                    |
|----------------------|-------------|------------------------------------------|
| `Pending`            | `PENDING`   | `CREATED`, `PAYMENT_METHOD_CHOSEN`       |
| `Paid`               | `PAID`      | `PAID`                                   |
| `Authorized`         | `AUTHORIZED`| `AUTHORIZED`                             |
| `Cancelled`          | `CANCELLED` | `CANCELED`                               |
| `Refunded`           | —           | `REFUNDED`                               |
| `PartiallyRefunded`  | —           | `PARTIALLY_REFUNDED`                     |
| `TimedOut`           | —           | `TIMEOUTED`                              |
| `Unknown`            | ostatní     | ostatní                                  |

### Refundace

```php
use Brick\Money\Money;

$res = $this->paymentGateway->comgate->refundPayment(
    transactionId: $order->transactionId,
    amount:        Money::of('150.00', 'CZK'),
);

if (!$res->ok) {
    // $res->errorCode, $res->errorMessage
}
```

### Storno (zrušení čekající platby)

```php
$res = $this->paymentGateway->comgate->stornoPayment($order->transactionId);

if (!$res->ok) {
    // chyba na straně brány
}
```

> **Pozor — GoPay:** `stornoPayment` u GoPay nevolá žádné API (GoPay zrušení čekající platby nepodporuje). Platební odkaz zůstane aktivní, dokud nevyprší. `$res->ok` bude `true`, ale `$res->gatewayNotified` bude `false`.
>
> Doporučené ošetření v notification handleru:
> ```php
> if ($order->isCancelled() && $status->status === PaymentStatus::Paid) {
>     // zákazník zaplatil za zrušenou objednávku → řeš přes účetnictví
> }
> ```

---

## Struktury odpovědí

### `PaymentResult`

| Property         | Typ             | Popis                              |
|------------------|-----------------|------------------------------------|
| `ok`             | `bool`          | `true` = úspěch                    |
| `transactionId`  | `string\|null`  | ID transakce z brány               |
| `redirectUrl`    | `string\|null`  | URL pro přesměrování zákazníka     |
| `errorCode`      | `string\|null`  | kód chyby (jen při `ok = false`)   |
| `errorMessage`   | `string\|null`  | popis chyby (jen při `ok = false`) |

### `PaymentStatusResult`

| Property        | Typ                    | Popis                              |
|-----------------|------------------------|------------------------------------|
| `ok`            | `bool`                 |                                    |
| `status`        | `PaymentStatus\|null`  | normalizovaný stav                 |
| `transactionId` | `string\|null`         |                                    |
| `refId`         | `string\|null`         | číslo objednávky                   |
| `amountInCents` | `int\|null`            | částka v haléřích                  |
| `currency`      | `string\|null`         | ISO 4217 (CZK, EUR, …)             |
| `errorCode`     | `string\|null`         |                                    |
| `errorMessage`  | `string\|null`         |                                    |

### `RefundResult`

| Property       | Typ            |
|----------------|----------------|
| `ok`           | `bool`         |
| `errorCode`    | `string\|null` |
| `errorMessage` | `string\|null` |

### `StornoResult`

| Property           | Typ            | Popis                                              |
|--------------------|----------------|----------------------------------------------------|
| `ok`               | `bool`         |                                                    |
| `gatewayNotified`  | `bool`         | `false` u GoPay — brána nebyla informována         |
| `errorCode`        | `string\|null` |                                                    |
| `errorMessage`     | `string\|null` |                                                    |

---

## Výjimky

Všechny výjimky rozšiřují `Ages\PaymentGateway\Exception\PaymentGatewayException`.

| Třída                            | Kdy nastane                                              |
|----------------------------------|----------------------------------------------------------|
| `GatewayInitializationException` | Nepodaří se sestavit klienta brány                       |
| `InvalidGatewayResponseException`| Brána vrátí neočekávaný formát odpovědi                  |

```php
use Ages\PaymentGateway\Exception\PaymentGatewayException;

try {
    $res = $this->paymentGateway->comgate->createPayment($payment);
} catch (PaymentGatewayException $e) {
    // infrastrukturní chyba (síť, neočekávaný formát, …)
    // chyba obchodní logiky (zamítnutá platba) je v $res->ok = false
}
```

---

## Architektura

```
src/
├── Config/
│   ├── ComgateConfig.php         konfigurace Comgate
│   ├── GoPayConfig.php           konfigurace GoPay
│   └── GatewayConfig.php         volitelný wrapper obou konfigurací
├── Entity/
│   ├── Enum/
│   │   └── PaymentStatus.php     normalizované stavy obou bran
│   ├── CreatePayment.php         vstupní entita pro vytvoření platby
│   ├── PaymentResult.php
│   ├── PaymentStatusResult.php
│   ├── RefundResult.php
│   └── StornoResult.php
├── Exception/
│   ├── PaymentGatewayException.php
│   ├── GatewayInitializationException.php
│   └── InvalidGatewayResponseException.php
├── Gateway/
│   ├── GatewayInterface.php      createPayment, paymentStatus, refundPayment, stornoPayment
│   ├── ComgateGateway.php
│   └── GoPayGateway.php
└── PaymentGateway.php            DI entry point — drží ComgateGateway|null + GoPayGateway|null
```
