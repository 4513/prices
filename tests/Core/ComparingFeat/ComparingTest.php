<?php

declare(strict_types=1);

namespace MiBo\Prices\Tests\ComparingFeat;

use MiBo\Prices\Calculators\PriceCalc;
use MiBo\Prices\Price;
use MiBo\Prices\Tests\TestingComparer;
use MiBo\Prices\Tests\VATResolver;
use MiBo\Prices\Tests\ComparingStatusEnum;
use MiBo\Prices\Units\Price\Currency;
use MiBo\Properties\Area;
use MiBo\Properties\Calculators\UnitConvertor;
use MiBo\Properties\Contracts\NumericalUnit;
use MiBo\Properties\Length;
use MiBo\Properties\LuminousIntensity;
use MiBo\Properties\Mass;
use MiBo\Properties\NumericalProperty;
use MiBo\Properties\ThermodynamicTemperature;
use MiBo\Properties\Units\Area\SquareMeter;
use MiBo\Properties\Units\Length\CentiMeter;
use MiBo\Properties\Units\Length\Meter;
use MiBo\Properties\Units\Length\MilliMeter;
use MiBo\Properties\Units\LuminousIntensity\Candela;
use MiBo\Properties\Units\Mass\KiloGram;
use MiBo\Properties\Units\ThermodynamicTemperature\DegreeCelsius;
use MiBo\VAT\Enums\VATRate;
use MiBo\VAT\Resolvers\ProxyResolver;
use MiBo\VAT\VAT;
use PHPUnit\Framework\TestCase;
use ValueError;

/**
 * Class ComparingTest
 *
 * @package MiBo\Prices\Tests\ComparingFeat
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 1.1
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 *
 * @coversDefaultClass \MiBo\Prices\Price
 */
class ComparingTest extends TestCase
{
    /**
     * @small
     *
     * @covers ::hasSameValueAs
     * @covers ::hasNotSameValueAs
     *
     * @param int|float $value
     * @param string $propertyClassName
     * @param \MiBo\Properties\Contracts\NumericalUnit $unit
     *
     * @return void
     *
     * @dataProvider provideSameValues
     */
    public function testValueEquality(int|float $value, string $propertyClassName, NumericalUnit $unit): void
    {
        $price    = new Price($value, Currency::get('EUR'));
        $property = new $propertyClassName($value, $unit);

        $this->assertTrue($price->hasSameValueAs($property));
        $this->assertFalse($price->hasNotSameValueAs($property));
    }

    /**
     * @small
     *
     * @covers ::isBetween
     * @covers ::isNotBetween
     * @covers ::isBetweenOrEqualTo
     * @covers ::isNotBetweenOrEqualTo
     *
     * @param \MiBo\Prices\Price $price
     * @param float|int $first
     * @param float|int $second
     *
     * @return void
     *
     * @dataProvider provideBetweenData
     */
    public function testBetween(Price $price, float|int $first, float|int $second): void
    {
        $this->assertTrue($price->isBetween($first, $second));
        $this->assertTrue($price->isBetweenOrEqualTo($first, $second));
        $this->assertFalse($price->isNotBetween($first, $second));
        $this->assertFalse($price->isNotBetweenOrEqualTo($first, $second));
    }

    /**
     * @small
     *
     * @covers ::isEqualTo
     * @covers ::isNotEqualTo
     * @covers ::isLessThan
     * @covers ::isNotLessThan
     * @covers ::isLessThanOrEqualTo
     * @covers ::isNotLessThanOrEqualTo
     * @covers ::isGreaterThan
     * @covers ::isNotGreaterThan
     * @covers ::isGreaterThanOrEqualTo
     * @covers ::isNotGreaterThanOrEqualTo
     * @covers ::isBetween
     * @covers ::isNotBetween
     * @covers ::isBetweenOrEqualTo
     * @covers ::isNotBetweenOrEqualTo
     * @covers ::is
     * @covers ::isNot
     *
     * @param \MiBo\Properties\NumericalProperty $property
     *
     * @return void
     *
     * @dataProvider provideIncompatibleData
     */
    public function testIncompatibleData(NumericalProperty $property, string $method): void
    {
        $price   = new Price(10, Currency::get('EUR'));
        $methods = [
            'isEqualTo',
            'isNotEqualTo',
            'isLessThan',
            'isNotLessThan',
            'isLessThanOrEqualTo',
            'isNotLessThanOrEqualTo',
            'isGreaterThan',
            'isNotGreaterThan',
            'isGreaterThanOrEqualTo',
            'isNotGreaterThanOrEqualTo',
            'isBetween',
            'isNotBetween',
            'isBetweenOrEqualTo',
            'isNotBetweenOrEqualTo',
        ];

        $this->expectException(ValueError::class);
        in_array($method, ['is', 'isNot']) ? $price->$method($property) : $price->$method($property, $property);
    }

    /**
     * @small
     *
     * @covers ::isEven
     * @covers ::isOdd
     * @covers ::isNotEven
     * @covers ::isNotOdd
     * @covers ::isInteger
     * @covers ::isNotInteger
     * @covers ::isFloat
     * @covers ::isNotFloat
     *
     * @param bool|null $isEven
     * @param \MiBo\Prices\Price $price
     *
     * @return void
     *
     * @dataProvider provideEvens
     */
    public function testEvenOrOdd(?bool $isEven, Price $price): void
    {
        if ($isEven === true) {
            $this->assertTrue($price->isEven());
            $this->assertFalse($price->isOdd());
            $this->assertTrue($price->isNotOdd());
            $this->assertFalse($price->isNotEven());
            $this->assertTrue($price->isInteger());
            $this->assertFalse($price->isNotInteger());
            $this->assertFalse($price->isFloat());
            $this->assertTrue($price->isNotFloat());
        } else if ($isEven === false) {
            $this->assertTrue($price->isOdd());
            $this->assertFalse($price->isEven());
            $this->assertTrue($price->isNotEven());
            $this->assertFalse($price->isNotOdd());
            $this->assertTrue($price->isInteger());
            $this->assertFalse($price->isNotInteger());
            $this->assertFalse($price->isFloat());
            $this->assertTrue($price->isNotFloat());
        } else {
            $this->assertFalse($price->isEven());
            $this->assertFalse($price->isOdd());
            $this->assertTrue($price->isNotEven());
            $this->assertTrue($price->isNotOdd());
            $this->assertFalse($price->isInteger());
            $this->assertTrue($price->isNotInteger());
            $this->assertTrue($price->isFloat());
            $this->assertFalse($price->isNotFloat());
        }
    }

    /**
     * @small
     *
     * @covers ::isEqualTo
     * @covers ::isNotEqualTo
     * @covers ::isLessThan
     * @covers ::isNotLessThan
     * @covers ::isLessThanOrEqualTo
     * @covers ::isNotLessThanOrEqualTo
     * @covers ::isGreaterThan
     * @covers ::isNotGreaterThan
     * @covers ::isGreaterThanOrEqualTo
     * @covers ::isNotGreaterThanOrEqualTo
     * @covers ::is
     * @covers ::isNot
     *
     * @param bool|null $isGreater
     * @param \MiBo\Prices\Price $price
     * @param array $values
     *
     * @return void
     *
     * @dataProvider provideDataForComparing
     */
    public function testEquality(
        ?bool $isGreater,
        Price $price,
        array $values
    ): void
    {
        foreach ($values as $value) {
            if ($isGreater === null) {
                $this->assertTrue($price->isEqualTo($value));
                $this->assertFalse($price->isNotEqualTo($value));
                $this->assertTrue($price->isLessThanOrEqualTo($value));
                $this->assertFalse($price->isNotLessThanOrEqualTo($value));
                $this->assertTrue($price->isGreaterThanOrEqualTo($value));
                $this->assertFalse($price->isNotGreaterThanOrEqualTo($value));
                $this->assertTrue($price->isNotLessThan($value));
                $this->assertFalse($price->isLessThan($value));
                $this->assertFalse($price->isGreaterThan($value));
                $this->assertTrue($price->isNotGreaterThan($value));
                $this->assertTrue($price->isBetweenOrEqualTo($value, 0));
                $this->assertFalse($price->isNotBetweenOrEqualTo($value, 0));
                $this->assertFalse($price->isNot($value));
                $this->assertFalse($price->is($value));
            } else if ($isGreater === true) {
                $this->assertTrue($price->isGreaterThan($value));
                $this->assertFalse($price->isNotGreaterThan($value));
                $this->assertTrue($price->isNotLessThan($value));
                $this->assertFalse($price->isLessThan($value));
                $this->assertTrue($price->isGreaterThanOrEqualTo($value));
                $this->assertFalse($price->isNotGreaterThanOrEqualTo($value));
                $this->assertTrue($price->isNotLessThanOrEqualTo($value));
                $this->assertFalse($price->isLessThanOrEqualTo($value));
                $this->assertTrue($price->isBetweenOrEqualTo($value, 0));
                $this->assertTrue($price->isNotEqualTo($value));
                $this->assertFalse($price->isEqualTo($value));
                $this->assertFalse($price->isNot($value));
                $this->assertFalse($price->is($value));
            } else if ($isGreater === false) {
                $this->assertTrue($price->isLessThan($value));
                $this->assertFalse($price->isNotLessThan($value));
                $this->assertTrue($price->isNotGreaterThan($value));
                $this->assertFalse($price->isGreaterThan($value));
                $this->assertTrue($price->isLessThanOrEqualTo($value));
                $this->assertFalse($price->isNotLessThanOrEqualTo($value));
                $this->assertTrue($price->isNotGreaterThanOrEqualTo($value));
                $this->assertFalse($price->isGreaterThanOrEqualTo($value));
                $this->assertTrue($price->isBetweenOrEqualTo($value, 0));
                $this->assertTrue($price->isNotEqualTo($value));
                $this->assertFalse($price->isEqualTo($value));
                $this->assertFalse($price->isNot($value));
                $this->assertFalse($price->is($value));
            }
        }
    }

    /**
     * @small
     *
     * @covers ::isEqualTo
     * @covers ::isNotEqualTo
     * @covers ::isWithVATEqualTo
     * @covers ::isWithVATNotEqualTo
     * @covers ::hasSameValueWithVATAs
     * @covers ::hasNotSameValueWithVATAs
     *
     * @param array $same
     * @param \MiBo\Prices\Price $first
     * @param \MiBo\Prices\Price|float|int $second
     *
     * @return void
     *
     * @dataProvider provideSamePrices
     */
    public function testEquality2(array $same, Price $first, Price|float|int $second): void
    {
        $this->assertSame(
            in_array(ComparingStatusEnum::PRICE_CHANGED_EQUALS, $same),
            $first->isEqualTo($second)
        );
        $this->assertSame(
            !in_array(ComparingStatusEnum::PRICE_CHANGED_EQUALS, $same),
            $first->isNotEqualTo($second)
        );

        $this->assertSame(
            in_array(ComparingStatusEnum::PRICE_WITH_VAT_EQUALS, $same),
            $first->isWithVATEqualTo($second)
        );
        $this->assertSame(
            !in_array(ComparingStatusEnum::PRICE_WITH_VAT_EQUALS, $same),
            $first->isWithVATNotEqualTo($second)
        );

        $this->assertSame(
            in_array(ComparingStatusEnum::VALUE_WITH_VAT_EQUALS, $same),
            $first->hasSameValueWithVATAs($second)
        );
        $this->assertSame(
            !in_array(ComparingStatusEnum::VALUE_WITH_VAT_EQUALS, $same),
            $first->hasNotSameValueWithVATAs($second)
        );

        $this->assertSame(
            false,
            $first->is($second)
        );
        $this->assertSame(
            false,
            $first->isNot($second)
        );

        $this->assertSame(
            false,
            $first->is($second, true)
        );
        $this->assertSame(
            false,
            $first->isNot($second, true)
        );
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
            } else if ($price->getUnit()->is($unit)) {
                return $price->getNumericalValue();
            }

            return $price->getNumericalValue()->divide(25);
        };

        PriceCalc::setComparerHelper(new TestingComparer());
    }

    public static function provideSamePrices(): array
    {
        return [
            'Returning exactly same prices'                                                                                    => [
                [
                    ComparingStatusEnum::PRICE_EQUALS,
                    ComparingStatusEnum::PRICE_WITH_VAT_EQUALS,
                    ComparingStatusEnum::PRICE_CHANGED_EQUALS,
                    ComparingStatusEnum::VALUE_WITH_VAT_EQUALS,
                    ComparingStatusEnum::IS_SAME,
                    ComparingStatusEnum::IS_SAME_STRICT,
                ],
                new Price(10, Currency::get("CZK"), VAT::get("CZE", VATRate::STANDARD, "1")),
                new Price(10, Currency::get("CZK"), VAT::get("CZE", VATRate::STANDARD, "1")),
            ],
            'Returning same prices with different currency (same on conversion)'                                               => [
                [
                    ComparingStatusEnum::PRICE_SMALLER,
                    ComparingStatusEnum::PRICE_WITH_VAT_EQUALS,
                    ComparingStatusEnum::PRICE_CHANGED_EQUALS,
                    ComparingStatusEnum::VALUE_WITH_VAT_EQUALS,
                    ComparingStatusEnum::IS_SAME,
                    ComparingStatusEnum::IS_SAME_STRICT,
                ],
                new Price(10, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
                new Price(250, Currency::get("CZK"), VAT::get("CZE", VATRate::STANDARD, "1")),
            ],
            'Returning not the same prices'                                                                                    => [
                [
                    ComparingStatusEnum::PRICE_EQUALS,
                    ComparingStatusEnum::PRICE_CHANGED_GREATER,
                ],
                new Price(10, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
                new Price(10, Currency::get("CZK"), VAT::get("CZE", VATRate::STANDARD, "1")),
            ],
            'Returning not the same prices in same currency'                                                                   => [
                [
                    ComparingStatusEnum::PRICE_SMALLER,
                    ComparingStatusEnum::PRICE_WITH_VAT_SMALLER,
                    ComparingStatusEnum::PRICE_CHANGED_SMALLER,
                ],
                new Price(10, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
                new Price(12, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "2")),
            ],
            'Returning same prices with different product classification category'                                             => [
                [
                    ComparingStatusEnum::PRICE_EQUALS,
                    ComparingStatusEnum::PRICE_WITH_VAT_EQUALS,
                    ComparingStatusEnum::PRICE_CHANGED_EQUALS,
                    ComparingStatusEnum::VALUE_WITH_VAT_EQUALS,
                    ComparingStatusEnum::IS_SAME,
                    ComparingStatusEnum::IS_SAME_STRICT,
                ],
                new Price(10, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
                new Price(10, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "2")),
            ],
            'Returning same prices with different product classification category and different currency'                      => [
                [
                    ComparingStatusEnum::PRICE_SMALLER,
                    ComparingStatusEnum::PRICE_WITH_VAT_EQUALS,
                    ComparingStatusEnum::PRICE_CHANGED_EQUALS,
                    ComparingStatusEnum::VALUE_WITH_VAT_EQUALS,
                    ComparingStatusEnum::IS_SAME,
                    ComparingStatusEnum::IS_SAME_STRICT,
                ],
                new Price(10, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
                new Price(250, Currency::get("CZK"), VAT::get("CZE", VATRate::STANDARD, "2")),
            ],
            'Returning same prices with different product classification category and different currency (same on conversion)' => [
                [
                    ComparingStatusEnum::PRICE_SMALLER,
                    ComparingStatusEnum::PRICE_WITH_VAT_EQUALS,
                    ComparingStatusEnum::PRICE_CHANGED_EQUALS,
                    ComparingStatusEnum::VALUE_WITH_VAT_EQUALS,
                    ComparingStatusEnum::IS_SAME,
                    ComparingStatusEnum::IS_SAME_STRICT,
                ],
                new Price(10, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
                new Price(250, Currency::get("CZK"), VAT::get("CZE", VATRate::STANDARD, "2")),
            ],
            'Returning same prices with different VAT rate'                                                                    => [
                [
                    ComparingStatusEnum::PRICE_EQUALS,
                    ComparingStatusEnum::PRICE_WITH_VAT_EQUALS,
                    ComparingStatusEnum::PRICE_CHANGED_EQUALS,
                    ComparingStatusEnum::VALUE_WITH_VAT_EQUALS,
                    ComparingStatusEnum::IS_SAME,
                    ComparingStatusEnum::IS_SAME_STRICT,
                ],
                new Price(10, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
                new Price(10, Currency::get("EUR"), VAT::get("CZE", VATRate::REDUCED, "1")),
            ],
            'Returning integer'                                                                                                => [
                [
                    ComparingStatusEnum::PRICE_EQUALS,
                    ComparingStatusEnum::PRICE_CHANGED_EQUALS,
                ],
                new Price(10, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
                10,
            ],
            'Returning integer same value with vat'                                                                            => [
                [
                    ComparingStatusEnum::PRICE_EQUALS,
                    ComparingStatusEnum::PRICE_WITH_VAT_EQUALS,
                    ComparingStatusEnum::VALUE_WITH_VAT_EQUALS,
                ],
                new Price(10, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
                12.1,
            ],
            'Returning integer same value'                                                                                     => [
                [
                    ComparingStatusEnum::PRICE_EQUALS,
                    ComparingStatusEnum::PRICE_CHANGED_EQUALS,
                    ComparingStatusEnum::VALUE_WITH_VAT_EQUALS,
                    ComparingStatusEnum::PRICE_WITH_VAT_EQUALS,
                    ComparingStatusEnum::IS_SAME,
                ],
                new Price(10, Currency::get("EUR"), VAT::get("CZE", VATRate::NONE, "10")),
                10,
            ],
        ];
    }

    public static function provideDataForComparing(): array
    {
        return [
            'Same'    => [
                null,
                new Price(10, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
                [
                    10,
                    10.0,
                    10.00,
                    10,
                ],
            ],
            'Less'    => [
                true,
                new Price(10, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
                [
                    9,
                    9.0,
                    9.00,
                    9,
                    9.99,
                    1,
                    -1,
                    0,
                    0.1,
                ],
            ],
            'Greater' => [
                false,
                new Price(10, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
                [
                    11,
                    11.0,
                    11.00,
                    11,
                    11.01,
                    100,
                    1000,
                    10000,
                    100000,
                    1000000,
                ],
            ],
        ];
    }

    public static function provideEvens(): array
    {
        return [
            [
                true,
                new Price(10, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
            ],
            [
                true,
                new Price(12, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
            ],
            [
                true,
                new Price(0, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
            ],
            [
                true,
                new Price(2, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
            ],
            [
                true,
                new Price(100, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
            ],
            [
                true,
                new Price(60, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
            ],
            [
                null,
                new Price(50.2, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
            ],
            [
                false,
                new Price(1, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
            ],
            [
                false,
                new Price(3, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
            ],
            [
                false,
                new Price(15, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
            ],
            [
                false,
                new Price(9, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
            ],
            [
                false,
                new Price(-1, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
            ],
            [
                false,
                new Price(1, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
            ],
            [
                false,
                new Price(01, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
            ],
            [
                null,
                new Price(10.1, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
            ],
            [
                null,
                new Price(10.2, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
            ],
            [
                null,
                new Price(-0.04, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
            ],
            [
                true,
                new Price(-10, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
            ],
            [
                true,
                new Price(-2, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
            ],
        ];
    }

    public static function provideIncompatibleData(): \Generator
    {
        $methods = [
            'isEqualTo',
            'isNotEqualTo',
            'isLessThan',
            'isNotLessThan',
            'isLessThanOrEqualTo',
            'isNotLessThanOrEqualTo',
            'isGreaterThan',
            'isNotGreaterThan',
            'isGreaterThanOrEqualTo',
            'isNotGreaterThanOrEqualTo',
            'isBetween',
            'isNotBetween',
            'isBetweenOrEqualTo',
            'isNotBetweenOrEqualTo',
            'is',
            'isNot',
        ];

        $data = [
            new Length(10, Meter::get()),
            new Mass(10, Kilogram::get()),
            new ThermodynamicTemperature(10, DegreeCelsius::get()),
            new LuminousIntensity(10, Candela::get()),
        ];

        /** @var \MiBo\Properties\Contracts\NumericalProperty $property */
        foreach ($data as $property) {
            $message = 'Testing incompatible type ' . $property->getQuantity()::getNameForTranslation();

            foreach ($methods as $method) {
                yield $message . ' method ' . $method => [
                    $property,
                    $method,
                ];
            }
        }
    }

    public static function provideBetweenData(): array
    {
        return [
            [
                new Price(10, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
                9,
                11,
            ],
            [
                new Price(10, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
                9.9,
                11,
            ],
            [
                new Price(10, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
                -1,
                10.1,
            ],
        ];
    }

    public static function provideSameValues(): array
    {
        return [
            [
                10,
                Length::class,
                Meter::get(),
            ],
            [
                10,
                Length::class,
                MilliMeter::get(),
            ],
            [
                5,
                Length::class,
                CentiMeter::get(),
            ],
            [
                1.1,
                Length::class,
                Meter::get(),
            ],
            [
                10,
                Area::class,
                SquareMeter::get(),
            ],
        ];
    }
}
