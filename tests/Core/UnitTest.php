<?php

declare(strict_types=1);

namespace MiBo\Prices\Tests;

use MiBo\Prices\Quantities\Price;
use MiBo\Prices\Units\Price\Currency;
use PHPUnit\Framework\TestCase;

/**
 * Class UnitTest
 *
 * @package MiBo\Prices\Tests
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 0.1
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 *
 * @coversDefaultClass \MiBo\Prices\Units\Price\Currency
 */
class UnitTest extends TestCase
{
    /**
     * @small
     *
     * @covers ::__construct
     * @covers ::get
     *
     * @return void
     */
    public function testCreation(): void
    {
        $currency = Currency::get('EUR');

        $this->assertInstanceOf(Currency::class, $currency);
        $this->assertSame("EUR", $currency->getAlphabeticalCode());

        $currency = Currency::get("CZK");

        $this->assertSame("CZK", $currency->getAlphabeticalCode());
    }

    /**
     * @small
     *
     * @covers ::getQuantityClassName
     * @covers ::getName
     * @covers ::getAlphabeticalCode
     * @covers ::getNumericalCode
     * @covers ::getMinorUnitRate
     * @covers ::setCurrencyProvider
     * @covers ::is
     * @covers ::get
     * @covers ::__debugInfo
     *
     * @return void
     */
    public function testCommonUnitMethods(): void
    {
        $currency = Currency::get();

        $this->assertSame(Price::class, Currency::getQuantityClassName());

        $this->assertSame("Euro", $currency->getName());
        $this->assertSame("EUR", $currency->getAlphabeticalCode());
        $this->assertSame("978", $currency->getNumericalCode());
        $this->assertSame(2, $currency->getMinorUnitRate());
        $this->assertNotNull($provider = Currency::setCurrencyProvider(null));
        $this->assertNull(Currency::setCurrencyProvider($provider));
        Currency::setCurrencyProvider(null);
        $this->assertTrue($currency->is($currency));

        Currency::get();
        $this->assertNotNull(Currency::setCurrencyProvider(null));

        $currency = Currency::get("EUR");
        $this->assertSame(
            [
                "name"             => "Euro",
                "alphabeticalCode" => "EUR",
                "numericalCode"    => "978",
                "minorUnitRate"    => 2,
            ],
            $currency->__debugInfo()
        );
    }
}
