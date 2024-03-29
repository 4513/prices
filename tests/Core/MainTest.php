<?php

declare(strict_types=1);

namespace MiBo\Prices\Tests;

use DateTime;
use MiBo\Prices\Calculators\PriceCalc;
use MiBo\Prices\Price;
use MiBo\Prices\Taxonomies\AnyTaxonomy;
use MiBo\Prices\Units\Price\Currency;
use MiBo\Properties\Calculators\UnitConvertor;
use MiBo\VAT\Enums\VATRate;
use MiBo\VAT\Manager;
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
            new Price(10, Currency::get("CZK"), $this->retrieveVATByCategory("9705 00 00", "CZE")),
            new Price(15, Currency::get("CZK"), $this->retrieveVATByCategory("9704 00 00", "CZE")),
            new Price(20, Currency::get("CZK"), $this->retrieveVATByCategory("2201", "CZE")),
            new Price(25, Currency::get("CZK"), $this->retrieveVATByCategory("2201", "CZE")),
            new Price(30, Currency::get("CZK"), $this->retrieveVATByCategory("07", "CZE")),
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
            new Price(1, Currency::get("EUR"), $this->retrieveVATByCategory("07", "SVK"))
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
     * @covers ::setValueOfVATClosure
     * @covers ::getValueOfVAT
     *
     * @return void
     */
    public function testNullGetValueOfVAT(): void
    {
        PriceCalc::setValueOfVATClosure(null);
        PriceCalc::getValueOfVAT(
            new Price(0, Currency::get('CZK'), $this->retrieveVATByCategory('06', 'CZE'))
        );

        PriceCalc::setValueOfVATClosure(function(): int {
            return 0;
        });

        $this->assertEquals(
            0,
            PriceCalc::getValueOfVAT(
                new Price(0, Currency::get('CZK'), $this->retrieveVATByCategory('06', 'CZE'))
            )
        );
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
        $price = new Price(10, Currency::get("CZK"), $this->retrieveVATByCategory("9705 00 00", "CZE"));
        $price->add(new Price(10, Currency::get("CZK"), $this->retrieveVATByCategory("07", "CZE")));

        $this->assertSame(20, $price->getValue());
        $this->assertSame(21.5, $price->getValueWithVAT());

        $price2 = new Price(10, Currency::get("CZK"), $this->retrieveVATByCategory("9705 00 00", "CZE"));
        $price2->add($price);

        $this->assertSame(30, $price2->getValue());
        $this->assertSame(33.0, $price2->getValueWithVAT());
    }

    /**
     * @small
     *
     * @covers ::add
     *
     * @return void
     */
    public function testAnyRateSubtraction(): void
    {
        $prices = [
            new Price(10, Currency::get("CZK"), $this->retrieveVATByCategory("9705 00 00", "CZE")),
            new Price(15, Currency::get("CZK"), $this->retrieveVATByCategory("9704 00 00", "CZE")),
            new Price(20, Currency::get("CZK"), $this->retrieveVATByCategory("2201", "CZE")),
            new Price(25, Currency::get("CZK"), $this->retrieveVATByCategory("2201", "CZE")),
            new Price(30, Currency::get("CZK"), $this->retrieveVATByCategory("07", "CZE")),
        ];

        $price = clone $prices[0];

        foreach ($prices as $p) {
            $price->add($p);
        }

        $discount = new Price(
            $price->getValue(),
            Currency::get("CZK"),
            VAT::get('CZE', VATRate::ANY, AnyTaxonomy::get(), new DateTime())
        );

        $price->subtract($discount);

        $this->assertSame(0, $price->getValue());
    }

    /**
     * @small
     *
     * @covers ::add
     *
     * @return void
     */
    public function testSubtraction(): void
    {
        $price = new Price(10, Currency::get("CZK"), $this->retrieveVATByCategory("9705 00 00", "CZE"));
        $price->subtract(new Price(10, Currency::get("CZK"), $this->retrieveVATByCategory("9705 00 00", "CZE")));

        $this->assertSame(0, $price->getValue());
        $this->assertSame(0, $price->getValueWithVAT());

        $price2 = new Price(10, Currency::get("CZK"), $this->retrieveVATByCategory("9705 00 00", "CZE"));
        $price2->subtract($price);

        $this->assertSame(10, $price2->getValue());
        $this->assertSame(11.5, $price2->getValueWithVAT());

        $prices = [
            new Price(10, Currency::get("CZK"), $this->retrieveVATByCategory("9705 00 00", "CZE")),
            new Price(15, Currency::get("CZK"), $this->retrieveVATByCategory("9704 00 00", "CZE")),
            new Price(20, Currency::get("CZK"), $this->retrieveVATByCategory("2201", "CZE")),
            new Price(25, Currency::get("CZK"), $this->retrieveVATByCategory("2201", "CZE")),
            new Price(30, Currency::get("CZK"), $this->retrieveVATByCategory("07", "CZE")),
        ];

        $price = clone $prices[0];
        $price->multiply(0);

        foreach ($prices as $p) {
            $price->add($p);
        }

        $this->assertSame(100, $price->getValue());

        $newPrice = clone $price;

        foreach ($prices as $p) {
            $currentPrice = $newPrice->getValue();

            $newPrice->subtract($p);
            $this->assertSame($currentPrice - $p->getValue(), $newPrice->getValue());
        }

        $this->assertSame(0, $newPrice->getValue());

        $newPrice = clone $price;

        $newPrice->subtract($price);

        $this->assertSame(0, $newPrice->getValue());
    }

    /**
     * @small
     *
     * @covers ::add
     *
     * @return void
     */
    public function testAddingForeignPrice(): void
    {
        $priceCZK = new Price(10, Currency::get("CZK"), $this->retrieveVATByCategory("9705 00 00", "CZE"));
        $priceEUR = new Price(10, Currency::get("EUR"), $this->retrieveVATByCategory("9705 00 00", "CZE"));

        [
            $price,
            $vat,
        ] = PriceCalc::add($priceCZK, $priceEUR);

        $this->assertSame(260, $price);
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $vatHelper = new VATResolver();

        PriceCalc::setVATManager(new Manager($vatHelper, $vatHelper, $vatHelper));

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

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        PriceCalc::setValueOfVATClosure(null);
        parent::tearDown();
    }

    protected function retrieveVATByCategory(string $category, string $country): VAT
    {
        return PriceCalc::getVATManager()->retrieveVAT(new TestingClassification($category), $country);
    }
}
