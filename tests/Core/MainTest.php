<?php

declare(strict_types=1);

namespace MiBo\Prices\Tests;

use MiBo\Prices\Price;
use MiBo\Prices\Units\Price\Currency;
use MiBo\Properties\Calculators\UnitConvertor;
use MiBo\VAT\Enums\VATRate;
use MiBo\VAT\Resolvers\ProxyResolver;
use MiBo\VAT\VAT;
use PHPUnit\Framework\TestCase;

/**
 * Class MainTest
 *
 * @package MiBo\Prices\Tests
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 0.1
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 *
 * @coversDefaultClass \MiBo\Prices\Calculators\PriceCalc
 */
class MainTest extends TestCase
{
    /**
     * @small
     *
     * @coversNothing
     *
     * @return void
     */
    public function testSomething(): void
    {
        $this->assertTrue(true);
    }

    /**
     * @small
     *
     * @covers ::add
     * @covers ::getValueOfVAT
     *
     * @return void
     */
    public function test(): void
    {
        $prices = [
            new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("9705 00 00", "CZE")),
            new Price(15, Currency::get("CZK"), ProxyResolver::retrieveByCategory("9704 00 00", "CZE")),
            new Price(20, Currency::get("CZK"), ProxyResolver::retrieveByCategory("2201", "CZE")),
            new Price(25, Currency::get("CZK"), ProxyResolver::retrieveByCategory("2201", "CZE")),
            new Price(30, Currency::get("CZK"), ProxyResolver::retrieveByCategory("07", "CZE")),
        ];

        $price = clone $prices[0];

        foreach ($prices as $p) {
            $price->add($p);
        }

        $this->assertSame(110, $price->getValue());
        $price->getValueOfVAT();
        $this->assertSame(9.75, $price->getValueOfVAT());
        $this->assertSame(119.75, $price->getValueWithVAT());

        $price->add(
            new Price(1, Currency::get("EUR"), ProxyResolver::retrieveByCategory("07", "SVK"))
        );

        $this->assertSame(135, $price->getValue());

        // Price with CZE VAT, because using already used VAT
        $this->assertSame(144.75, $price->getValueWithVAT());

        $price->add(clone $price);

        $this->assertSame(270, $price->getValue());
        $this->assertSame(289.5, $price->getValueWithVAT());
    }

    /**
     * @small
     *
     * @covers ::add
     *
     * @return void
     */
    public function testAddingCombined(): void
    {
        $price = new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("9705 00 00", "CZE"));
        $price->add(new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("07", "CZE")));

        $this->assertSame(20, $price->getValue());
        $this->assertSame(21.5, $price->getValueWithVAT());

        $price2 = new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("9705 00 00", "CZE"));
        $price2->add($price);

        $this->assertSame(30, $price2->getValue());
        $this->assertSame(33.0, $price2->getValueWithVAT());
    }

    /**
     * @small
     *
     * @covers ::subtract
     *
     * @return void
     */
    public function testAnyRateSubtraction(): void
    {
        $prices = [
            new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("9705 00 00", "CZE")),
            new Price(15, Currency::get("CZK"), ProxyResolver::retrieveByCategory("9704 00 00", "CZE")),
            new Price(20, Currency::get("CZK"), ProxyResolver::retrieveByCategory("2201", "CZE")),
            new Price(25, Currency::get("CZK"), ProxyResolver::retrieveByCategory("2201", "CZE")),
            new Price(30, Currency::get("CZK"), ProxyResolver::retrieveByCategory("07", "CZE")),
        ];

        $price = clone $prices[0];

        foreach ($prices as $p) {
            $price->add($p);
        }

        $discount = new Price($price->getValue(), Currency::get("CZK"), VAT::get("CZE", VATRate::ANY));

        $price->subtract($discount);

        $this->assertSame(0, $price->getValue());
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        ProxyResolver::setResolver(VATResolver::class);

        // Setting conversion rate between CZK and EUR => 1 EUR = 25 CZK
        UnitConvertor::$unitConvertors[\MiBo\Prices\Quantities\Price::class] = function(Price $price, Currency $unit) {
            if ($price->getUnit()->getName() === "Euro" && $unit->getName() === "Czech Koruna") {
                return $price->getNumericalValue()->multiply(25);
            } elseif ($price->getUnit()->is($unit)) {
                return $price->getNumericalValue();
            }

            return $price->getNumericalValue()->divide(25);
        };
    }
}
