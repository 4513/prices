<?php

declare(strict_types=1);

namespace MiBo\Prices\Tests\ComparingFeat;

use MiBo\Prices\Price;
use MiBo\Prices\Tests\VATResolver;
use MiBo\Prices\Tests\ComparingStatusEnum;
use MiBo\Prices\Units\Price\Currency;
use MiBo\Properties\Calculators\UnitConvertor;
use MiBo\VAT\Enums\VATRate;
use MiBo\VAT\Resolvers\ProxyResolver;
use MiBo\VAT\VAT;
use PHPUnit\Framework\TestCase;

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
     * @covers ::isEqualTo
     * @covers ::isNotEqualTo
     * @covers ::isWithVATEqualTo
     * @covers ::isWithVATNotEqualTo
     * @covers ::hasSameValueWithVATAs
     * @covers ::hasNotSameValueWithVATAs
     * @covers ::is
     *
     * @param array $same
     * @param \MiBo\Prices\Price $first
     * @param \MiBo\Prices\Price|float|int $second
     *
     * @return void
     *
     * @dataProvider provideSamePrices
     */
    public function testEquality(array $same, Price $first, Price|float|int $second): void
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
            in_array(ComparingStatusEnum::IS_SAME, $same),
            $first->is($second)
        );
        $this->assertSame(
            !in_array(ComparingStatusEnum::IS_SAME, $same),
            $first->isNot($second)
        );

        $this->assertSame(
            in_array(ComparingStatusEnum::IS_SAME_STRICT, $same),
            $first->is($second, true)
        );
        $this->assertSame(
            !in_array(ComparingStatusEnum::IS_SAME_STRICT, $same),
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
            } elseif ($price->getUnit()->is($unit)) {
                return $price->getNumericalValue();
            }

            return $price->getNumericalValue()->divide(25);
        };
    }

    public static function provideSamePrices(): array
    {
        return [
            'Returning exactly same prices' => [
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
            'Returning same prices with different currency (same on conversion)' => [
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
            'Returning not the same prices' => [
                [
                    ComparingStatusEnum::PRICE_EQUALS,
                    ComparingStatusEnum::PRICE_CHANGED_GREATER,
                ],
                new Price(10, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
                new Price(10, Currency::get("CZK"), VAT::get("CZE", VATRate::STANDARD, "1")),
            ],
            'Returning not the same prices in same currency' => [
                [
                    ComparingStatusEnum::PRICE_SMALLER,
                    ComparingStatusEnum::PRICE_WITH_VAT_SMALLER,
                    ComparingStatusEnum::PRICE_CHANGED_SMALLER,
                ],
                new Price(10, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
                new Price(12, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "2")),
            ],
            'Returning same prices with different product classification category' => [
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
            'Returning same prices with different product classification category and different currency' => [
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
            'Returning same prices with different VAT rate' => [
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
            'Returning integer' => [
                [
                    ComparingStatusEnum::PRICE_EQUALS,
                    ComparingStatusEnum::PRICE_CHANGED_EQUALS,
                ],
                new Price(10, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
                10,
            ],
            'Returning integer same value with vat' => [
                [
                    ComparingStatusEnum::PRICE_EQUALS,
                    ComparingStatusEnum::PRICE_WITH_VAT_EQUALS,
                    ComparingStatusEnum::VALUE_WITH_VAT_EQUALS,
                ],
                new Price(10, Currency::get("EUR"), VAT::get("CZE", VATRate::STANDARD, "1")),
                12.1,
            ],
            'Returning integer same value' => [
                [
                    ComparingStatusEnum::PRICE_EQUALS,
                    ComparingStatusEnum::PRICE_CHANGED_EQUALS,
                    ComparingStatusEnum::VALUE_WITH_VAT_EQUALS,
                    ComparingStatusEnum::PRICE_WITH_VAT_EQUALS,
                    ComparingStatusEnum::IS_SAME,
                ],
                new Price(10, Currency::get("EUR"), VAT::get("CZE", VATRate::NONE, "10")),
                10,
            ]
        ];
    }
}
