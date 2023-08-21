<?php

declare(strict_types=1);

namespace MiBo\Prices\Tests\Core\PriceProperty;

use DateTime;
use MiBo\Prices\Price;
use MiBo\Prices\Units\Price\Currency;
use MiBo\Properties\Value;
use MiBo\VAT\Enums\VATRate;
use PHPUnit\Framework\TestCase;

/**
 * Class CreationTest
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
class CreationTest extends TestCase
{
    /**
     * @small
     *
     * @covers ::__construct
     * @covers ::getValue
     * @covers ::getBaseValue
     * @covers ::getDateTime
     * @covers ::getUnit
     * @covers ::__debugInfo
     *
     * @return void
     */
    public function testConstruct(): void
    {
        $price = new Price(50, Currency::get("EUR"));

        $this->assertSame(VATRate::NONE, $price->getVAT()->getRate());
        $this->assertSame(50, $price->getValue());
        $this->assertSame("Euro", $price->getUnit()->getName());

        $price = new Price(new Value(50, 2), Currency::get());

        $this->assertSame(50, $price->getValue());
        $this->assertSame($price->getValue(), $price->getBaseValue());
        $this->assertNull($price->getDateTime());

        $time  = new DateTime();
        $price = new Price(50, Currency::get("EUR"), null, $time);

        $this->assertEquals($time->getTimestamp(), $price->getDateTime()->getTimestamp());

        $this->assertSame(
            [
                'price'        => 50,
                'priceWithVAT' => 50,
                'valueOfVAT'   => 0,
                'currency'     => "EUR",
                'VAT'          => VATRate::NONE->name,
                'time'         => $time->format(DateTime::ATOM),
            ],
            array_filter($price->__debugInfo(), function(string $key): bool {
                return $key !== 'details';
            }, ARRAY_FILTER_USE_KEY)
        );
    }
}
