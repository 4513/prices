<?php

declare(strict_types=1);

namespace MiBo\Prices\Tests;

use MiBo\Prices\Price;
use MiBo\Prices\Units\Price\Currency;
use MiBo\Properties\Calculators\UnitConvertor;
use MiBo\VAT\Resolvers\ProxyResolver;
use PHPUnit\Framework\TestCase;

/**
 * Class PriceSubtractionTest
 *
 * @package MiBo\Prices\Tests
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 0.1
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 *
 * @coversDefaultClass \MiBo\Prices\Price
 */
class PriceSubtractionTest extends TestCase
{
    /**
     * @small
     *
     * @covers ::subtract
     * @covers ::compute
     *
     * @return void
     */
    public function testSubtractingCombinedOnCombined(): void
    {
        $price = new Price(100, Currency::get("CZK"), ProxyResolver::retrieveByCategory("9705 00 00", "CZE"));
        $price->subtract(new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("07", "CZE")));
        $price->subtract(new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("07", "CZE")));

        $this->assertSame(80, $price->getValue());

        $price = (new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("2201", "CZE")))
            ->subtract($price);

        $price->subtract(clone $price);

        $price->getValue();
        $this->assertSame(0, $price->getValue());
        $this->assertTrue($price->getVAT()->getRate()->isCombined());
    }

    /**
     * @small
     *
     * @covers ::subtract
     * @covers ::compute
     *
     * @return void
     */
    public function testSubtractingCombined(): void
    {
        $price = new Price(100, Currency::get("CZK"), ProxyResolver::retrieveByCategory("9705 00 00", "CZE"));
        $price->subtract(new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("07", "CZE")));
        $price->subtract(new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("07", "CZE")));

        $this->assertSame(80, $price->getValue());

        $price = (new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("2201", "CZE")))
            ->subtract($price);

        $this->assertSame(-70, $price->getValue());
        $this->assertTrue($price->getVAT()->getRate()->isCombined());
    }

    /**
     * @small
     *
     * @covers ::subtract
     * @covers ::compute
     *
     * @return void
     */
    public function testSubtractingOnCombined(): void
    {
        $price = new Price(100, Currency::get("CZK"), ProxyResolver::retrieveByCategory("9705 00 00", "CZE"));
        $price->subtract(new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("07", "CZE")));
        $price->subtract(new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("07", "CZE")));

        $this->assertSame(80, $price->getValue());
        $this->assertSame(15.0, $price->getValueOfVAT());

        $price->subtract(new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("2201", "CZE")));

        $this->assertSame(70, $price->getValue());
        $this->assertTrue($price->getVAT()->getRate()->isCombined());
        $this->assertSame(14.0, $price->getValueOfVAT());
    }

    /**
     * @small
     *
     * @covers ::subtract
     * @covers ::compute
     *
     * @return void
     */
    public function testSubtractingDifferentVAT(): void
    {
        $price = new Price(100, Currency::get("CZK"), ProxyResolver::retrieveByCategory("9705 00 00", "CZE"));

        $this->assertSame(100, $price->getValue());
        $this->assertSame(100, $price->getNumericalValue()->getValue(2));
        $this->assertSame(15.0, $price->getValueOfVAT());

        $price->subtract(new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("07", "CZE")));

        $this->assertSame(90, $price->getValue());
        $this->assertSame(90, $price->getValue());
        $this->assertSame(90, $price->getNumericalValue()->getValue(2));

        $this->assertTrue($price->getVAT()->getRate()->isCombined());
        $this->assertSame(15.0, $price->getValueOfVAT());
    }

    /**
     * @small
     *
     * @covers ::subtract
     * @covers ::compute
     *
     * @return void
     */
    public function testSubtractingSameVAT(): void
    {
        $price = new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("9705 00 00", "CZE"));

        $this->assertSame(10, $price->getValue());
        $this->assertSame(10, $price->getNumericalValue()->getValue(2));

        $price->subtract(new Price(5, Currency::get("CZK"), ProxyResolver::retrieveByCategory("9705 00 00", "CZE")));

        $this->assertSame(5, $price->getValue());
        $this->assertSame(5, $price->getNumericalValue()->getValue(2));
        $this->assertSame(5 * 0.15, $price->getValueOfVAT());
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
