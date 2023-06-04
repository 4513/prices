<?php

declare(strict_types=1);

namespace MiBo\Prices\Units\Price;

use MiBo\Currencies\CurrencyInterface;
use MiBo\Currencies\CurrencyProvider;
use MiBo\Currencies\ISO\ISOCurrencyProvider;
use MiBo\Currencies\ISO\ISOListLoader;
use MiBo\Prices\Quantities\Price;
use MiBo\Properties\Contracts\NumericalUnit;
use MiBo\Properties\Contracts\Unit;
use MiBo\Properties\Traits\NotAcceptedBySIUnit;
use MiBo\Properties\Traits\NotAstronomicalUnit;
use MiBo\Properties\Traits\NotEnglishUnit;
use MiBo\Properties\Traits\NotImperialUnit;
use MiBo\Properties\Traits\NotInternationalSystemUnit;
use MiBo\Properties\Traits\NotMetricUnit;
use MiBo\Properties\Traits\NotUSCustomaryUnit;
use MiBo\Properties\Traits\UnitHelper;
use Psr\Log\NullLogger;

/**
 * Class Currency
 *
 * @package MiBo\Prices\Units\Price
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 0.1
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
class Currency implements NumericalUnit, CurrencyInterface
{
    use NotInternationalSystemUnit;
    use NotImperialUnit;
    use NotMetricUnit;
    use NotAcceptedBySIUnit;
    use NotAstronomicalUnit;
    use NotUSCustomaryUnit;
    use NotEnglishUnit;
    use UnitHelper {
        get as contractGet;
        is as contractIs;
        getName as contractGetName;
    }

    private CurrencyInterface $currency;

    protected static array $instances = [];

    public static string $defaultCurrency = 'EUR';

    private static ?CurrencyProvider $currencyProvider = null;

    public function __construct(?CurrencyInterface $currency = null)
    {
        $this->currency           = $currency;
        self::$currencyProvider ??= new ISOCurrencyProvider(new ISOListLoader(), new NullLogger());
    }

    public static function get(?string $currencyCode = null): static
    {
        $currencyCode = $currencyCode ?? static::$defaultCurrency;

        if (!isset(self::$instances[$currencyCode])) {
            self::$instances[$currencyCode] = new self(
                self::$currencyProvider->findByAlphabeticalCode($currencyCode)
            );
        }

        return self::$instances[$currencyCode];
    }

    public static function getQuantityClassName(): string
    {
        return Price::class;
    }

    public static function setCurrencyProvider(?CurrencyProvider $currencyProvider): ?CurrencyProvider
    {
        $current                = self::$currencyProvider;
        self::$currencyProvider = $currencyProvider;

        return $current;
    }

    public function getAlphabeticalCode(): string
    {
        return $this->currency->getAlphabeticalCode();
    }

    public function getNumericalCode(): string
    {
        return $this->currency->getNumericalCode();
    }

    public function getMinorUnitRate(): ?int
    {
        return $this->currency->getMinorUnitRate();
    }

    public function getName(): string
    {
        return $this->currency->getName();
    }

    public function is(Unit|CurrencyInterface $unit): bool
    {
        if (!$unit instanceof CurrencyInterface) {
            return false;
        }

        return $this->currency->is($unit);
    }
}
