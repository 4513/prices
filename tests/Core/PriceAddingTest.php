<?php

declare(strict_types=1);

namespace MiBo\Prices\Tests;

use MiBo\Prices\Price;
use MiBo\Prices\Units\Price\Currency;
use MiBo\Properties\Calculators\UnitConvertor;
use MiBo\VAT\Resolvers\ProxyResolver;
use PHPUnit\Framework\TestCase;

/**
 * Class PriceAddingTest
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
class PriceAddingTest extends TestCase
{
    /**
     * @small
     *
     * @covers ::add
     * @covers ::compute
     *
     * @return void
     */
    public function testAddingCombinedOnCombined(): void
    {
        $price = new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("9705 00 00", "CZE"));
        $price->add(new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("07", "CZE")));
        $price->add(new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("07", "CZE")));

        $this->assertSame(30, $price->getValue());

        $price = (new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("2201", "CZE")))
            ->add($price);
        $price->add(clone $price);

        $this->assertSame(80, $price->getValue());
        $this->assertTrue($price->getVAT()->getRate()->isCombined());
    }

    /**
     * @small
     *
     * @covers ::add
     * @covers ::compute
     *
     * @return void
     */
    public function testAddingCombined(): void
    {
        $price = new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("9705 00 00", "CZE"));
        $price->add(new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("07", "CZE")));
        $price->add(new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("07", "CZE")));

        $this->assertSame(30, $price->getValue());

        $price = (new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("2201", "CZE")))
            ->add($price);

        $this->assertSame(40, $price->getValue());
        $this->assertTrue($price->getVAT()->getRate()->isCombined());
    }

    /**
     * @small
     *
     * @covers ::add
     * @covers ::compute
     *
     * @return void
     */
    public function testAddingOnCombined(): void
    {
        $price = new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("9705 00 00", "CZE"));
        $price->add(new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("07", "CZE")));
        $price->add(new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("07", "CZE")));

        $this->assertSame(30, $price->getValue());
        $this->assertSame(1.5, $price->getValueOfVAT());

        $price->add(new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("2201", "CZE")));

        $this->assertSame(40, $price->getValue());
        $this->assertTrue($price->getVAT()->getRate()->isCombined());
        $this->assertSame(2.5, $price->getValueOfVAT());
    }

    /**
     * @small
     *
     * @covers ::add
     * @covers ::compute
     *
     * @return void
     */
    public function testAddingDifferentVAT(): void
    {
        $price = new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("9705 00 00", "CZE"));

        $this->assertSame(10, $price->getValue());
        $this->assertSame(10, $price->getNumericalValue()->getValue(2));
        $this->assertSame(1.5, $price->getValueOfVAT());

        $price->add(new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("07", "CZE")));

        $this->assertSame(20, $price->getValue());
        $this->assertSame(20, $price->getValue());
        $this->assertSame(20, $price->getNumericalValue()->getValue(2));

        $this->assertTrue($price->getVAT()->getRate()->isCombined());
        $this->assertSame(1.5, $price->getValueOfVAT());
    }

    /**
     * @small
     *
     * @covers ::add
     * @covers ::compute
     *
     * @return void
     */
    public function testAddingSameVAT(): void
    {
        $price = new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("9705 00 00", "CZE"));

        $this->assertSame(10, $price->getValue());
        $this->assertSame(10, $price->getNumericalValue()->getValue(2));

        $price->add(new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("9705 00 00", "CZE")));

        $this->assertSame(20, $price->getValue());
        $this->assertSame(20, $price->getNumericalValue()->getValue(2));

        $this->assertTrue($price->getVAT()->getRate()->isReduced());
        $this->assertSame(3.0, $price->getValueOfVAT());
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
