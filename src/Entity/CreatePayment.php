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
        private readonly string|null $language,
        private readonly string|null $country,
    ) {
    }

    /**
     * @param string|null $language jazyk platební stránky, ISO 639-1 (sk); null nebo jazyk, který brána neumí = výchozí jazyk brány
     * @param string|null $country  země plátce, ISO 3166-1 alpha-2 (SK); Comgate podle ní nabídne platební metody, null = všechny země
     */
    public static function of(
        Money $money,
        string $label,
        string $refId,
        string $email,
        string $fullName,
        string|null $language = null,
        string|null $country = null,
    ): self {
        return new self(
            $money,
            $label,
            $refId,
            $email,
            $fullName,
            self::normalize($language, uppercase: false),
            self::normalize($country, uppercase: true),
        );
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

    /** ISO 639-1 malými písmeny (sk), null = výchozí jazyk brány */
    public function getLanguage(): string|null
    {
        return $this->language;
    }

    /** ISO 3166-1 alpha-2 velkými písmeny (SK), null = bez omezení na zemi */
    public function getCountry(): string|null
    {
        return $this->country;
    }

    private static function normalize(string|null $code, bool $uppercase): string|null
    {
        $code = trim((string) $code);
        if ($code === '') {
            return null;
        }
        return $uppercase ? strtoupper($code) : strtolower($code);
    }

}
