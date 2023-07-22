<?php

declare(strict_types=1);

namespace MiBo\Prices\Tests\Core\PriceProperty;

use MiBo\Prices\Price;
use MiBo\Prices\Units\Price\Currency;
use MiBo\Properties\Length;
use MiBo\VAT\Enums\VATRate;
use MiBo\VAT\VAT;
use PHPUnit\Framework\TestCase;

/**
 * Class CombinationTest
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
class CombinationTest extends TestCase
{
    /**
     * @small
     *
     * @covers ::add
     *
     * @return void
     */
    public function testAddingIncorrectProperty(): void
    {
        $price = new Price(100, Currency::get());

        $this->expectException(\ValueError::class);

        $price->add(Length::DECI(10));
    }

    /**
     * @small
     *
     * @covers ::add
     *
     * @return void
     */
    public function testAddingIntOnCombinedVAT(): void
    {
        $price = new Price(100, Currency::get(), VAT::get("SVK", VATRate::COMBINED));

        $this->expectException(\ValueError::class);

        $price->add(10);
    }

    /**
     * @small
     *
     * @covers ::add
     *
     * @return void
     */
    public function testAddingInt(): void
    {
        $price = new Price(10, Currency::get());

        $price->add(10);

        $this->assertSame(20, $price->getValue());
    }

    /**
     * @small
     *
     * @covers ::subtract
     *
     * @return void
     */
    public function testSubtractIntOnCombinedVAT(): void
    {
        $price = new Price(100, Currency::get(), VAT::get("SVK", VATRate::COMBINED));

        $this->expectException(\ValueError::class);

        $price->subtract(10);
    }

    /**
     * @small
     *
     * @covers ::subtract
     *
     * @return void
     */
    public function testSubtractInt(): void
    {
        $price = new Price(10, Currency::get());

        $price->subtract(5);

        $this->assertSame(5, $price->getValue());
    }
}
