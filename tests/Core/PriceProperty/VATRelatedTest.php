<?php

declare(strict_types=1);

namespace MiBo\Prices\Tests\Core\PriceProperty;

use DateTime;
use MiBo\Prices\Calculators\PriceCalc;
use MiBo\Prices\Price;
use MiBo\Prices\Tests\TestingClassification;
use MiBo\Prices\Tests\VATResolver;
use MiBo\Prices\Units\Price\Currency;
use MiBo\VAT\Enums\VATRate;
use MiBo\VAT\Manager;
use MiBo\VAT\VAT;
use PHPUnit\Framework\TestCase;

/**
 * Class VATRelatedTest
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
class VATRelatedTest extends TestCase
{
    /**
     * @small
     *
     * @covers ::getVAT
     *
     * @return void
     */
    public function testVAT(): void
    {
        $price = new Price(100, Currency::get());

        $this->assertTrue($price->getVAT()->getRate()->isAny());
    }

    /**
     * @small
     *
     * @covers ::getValueOfVAT
     * @covers ::getValueWithVAT
     *
     * @return void
     */
    public function testVATValue(): void
    {
        $price = new Price(100, Currency::get());

        $this->assertEquals(0, $price->getValueOfVAT());
        $this->assertSame(100, $price->getValueWithVAT());

        $price = new Price(
            100,
            Currency::get(),
            VAT::get("CZE", VATRate::STANDARD, new TestingClassification('21'), new DateTime())
        );

        $this->assertSame(21.0, $price->getValueOfVAT());
        $this->assertSame(121.0, $price->getValueWithVAT());
    }

    /**
     * @small
     *
     * @covers ::forCountry
     *
     * @return void
     */
    public function testConvertVAT(): void
    {
        $price = new Price(
            100,
            Currency::get(),
            VAT::get("CZE", VATRate::STANDARD, new TestingClassification('21'), new DateTime())
        );

        $this->assertSame(121.0, $price->getValueWithVAT());

        $price->forCountry("SVK");

        $this->assertSame(120.0, $price->getValueWithVAT());
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $vatHelper = new VATResolver();

        PriceCalc::setVATManager(new Manager($vatHelper, $vatHelper, $vatHelper));
    }
}
