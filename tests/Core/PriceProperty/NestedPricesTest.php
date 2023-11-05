<?php

declare(strict_types=1);

namespace MiBo\Prices\Tests\Core\PriceProperty;

use DateTime;
use MiBo\Prices\Price;
use MiBo\Prices\Tests\TestingClassification;
use MiBo\Prices\Units\Price\Currency;
use MiBo\VAT\Enums\VATRate;
use MiBo\VAT\VAT;
use PHPUnit\Framework\TestCase;

/**
 * Class NestedPricesTest
 *
 * @package MiBo\Prices\Tests\Core\PriceProperty
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 0.1
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 *
 * @coversDefaultClass \MiBo\Prices\Price
 */
class NestedPricesTest extends TestCase
{
    /**
     * @small
     *
     * @covers ::getVAT
     * @covers ::__clone
     * @covers \MiBo\Prices\Traits\PriceHelper::setNestedPrice
     * @covers \MiBo\Prices\Traits\PriceHelper::getNestedPrice
     * @covers \MiBo\Prices\Traits\PriceHelper::getNestedPrices
     *
     * @return void
     */
    public function testNesting(): void
    {
        $price = new Price(100, Currency::get(), VAT::get(
            "CZE",
            VATRate::STANDARD,
            new TestingClassification("food"),
            new DateTime()
        ));
        $price->add(new Price(1, Currency::get()));

        $this->assertSame(101, $price->getValue());
        $this->assertNotEmpty($price->getNestedPrices());
        $this->assertInstanceOf(Price::class, $price->getNestedPrice("food"));
    }
}
