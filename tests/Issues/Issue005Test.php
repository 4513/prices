<?php

declare(strict_types=1);

namespace MiBo\Prices\Tests\Issues;

use MiBo\Prices\Price;
use MiBo\Prices\Tests\VATResolver;
use MiBo\Prices\Units\Price\Currency;
use PHPUnit\Framework\TestCase;

/**
 * Class Issue005Test
 *
 * @package MiBo\Prices\Tests\Issues
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 2.0.1
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class Issue005Test extends TestCase
{
    /**
     * @small
     *
     * @coversNothing
     *
     * @return void
     */
    public function test(): void
    {
        $price = new Price(100, Currency::get('EUR'), VATResolver::retrieveByCategory('1', 'SVK'));

        self::assertEquals(100, $price->getValue());
        self::assertEquals(100, $price->getValue());

        $newPrice = new Price(100, Currency::get('EUR'), VATResolver::retrieveByCategory('1', 'SVK'));

        self::assertEquals(100, $newPrice->getValue());
        self::assertEquals(100, $newPrice->getValue());

        $combined = $price->add($newPrice);

        self::assertEquals(200, $combined->getValue());
        self::assertEquals(200, $combined->getValue());
        self::assertEquals(200, $price->getValue());

        $differentVat = new Price(100, Currency::get('EUR'), VATResolver::retrieveByCategory('2', 'SVK'));

        $price->add($differentVat);

        self::assertEquals(300, $price->getValue());
        self::assertEquals(360, $price->getValueWithVAT());
    }
}
