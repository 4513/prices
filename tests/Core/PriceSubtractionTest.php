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
    public function test(): void
    {
        $price = new Price(10, Currency::get("CZK"), ProxyResolver::retrieveByCategory("9705 00 00", "CZE"));

        $this->assertSame(10, $price->getValue());
        $this->assertSame(10, $price->getNumericalValue()->getValue(2));
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
