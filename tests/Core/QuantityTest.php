<?php

declare(strict_types=1);

namespace MiBo\Prices\Tests;

use MiBo\Prices\Quantities\Price;
use MiBo\Prices\Units\Price\Currency;
use PHPUnit\Framework\TestCase;

/**
 * Class QuantityTest
 *
 * @package MiBo\Prices\Tests
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 0.1
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 *
 * @coversDefaultClass \MiBo\Prices\Quantities\Price
 */
class QuantityTest extends TestCase
{
    /**
     * @small
     *
     * @covers ::getDimensionSymbol
     * @covers ::getDefaultProperty
     * @covers ::getInitialUnit
     * @covers ::getNameForTranslation
     *
     * @return void
     */
    public function testCommonQuantityMethods(): void
    {
        $this->assertSame("PRICE", Price::getDimensionSymbol());
        $this->assertSame(\MiBo\Prices\Price::class, Price::getDefaultProperty());
        $this->assertInstanceOf(Currency::class, Price::getDefaultUnit());
        $this->assertSame('price', Price::getNameForTranslation());
    }
}
