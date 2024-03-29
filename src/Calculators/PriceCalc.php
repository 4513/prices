<?php

declare(strict_types=1);

namespace MiBo\Prices\Calculators;

use BadMethodCallException;
use Closure;
use DomainException;
use MiBo\Prices\Contracts\PriceCalculatorHelper;
use MiBo\Prices\Contracts\PriceComparer;
use MiBo\Prices\Contracts\PriceInterface;
use MiBo\Prices\Taxonomies\CombinedTaxonomy;
use MiBo\VAT\Enums\VATRate;
use MiBo\VAT\Manager;
use MiBo\VAT\VAT;
use ValueError;

/**
 * Class PriceCalc
 *
 * @package MiBo\Prices\Calculators
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 0.1
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 *
 * @mixin \MiBo\Prices\Contracts\PriceCalculatorHelper
 * @mixin \MiBo\Prices\Contracts\PriceComparer
 */
class PriceCalc
{
    private static ?PriceCalculatorHelper $calculatorHelper = null;

    private static ?PriceComparer $comparerHelper = null;

    private static ?Manager $vatManager = null;

    /** @var \Closure(\MiBo\Prices\Contracts\PriceInterface): (int|float)|null */
    public static ?Closure $getValueOfVAT = null;

    /**
     * Returns the value of the VAT of the amount.
     *
     * @param \MiBo\Prices\Contracts\PriceInterface $price
     *
     * @return int|float
     */
    final public static function getValueOfVAT(PriceInterface $price): int|float
    {
        if ($price->getVAT()->isNone() || $price->getVAT()->isCombined() || $price->getVAT()->isAny()) {
            return 0;
        }

        if (self::$getValueOfVAT === null) {
            self::$getValueOfVAT = static function(PriceInterface $price): int|float {
                $minorUnitRate = $price->getUnit()->getMinorUnitRate() ?? 0;
                $vatValue      = round(
                    $price->getNumericalValue()->getValue($price->getUnit()->getMinorUnitRate() ?? 0)
                    * (self::$vatManager?->getValueOfVAT($price->getVAT()) ?? 1),
                    $minorUnitRate
                );

                return $minorUnitRate === 0 ? (int) $vatValue : $vatValue;
            };
        }

        return (self::$getValueOfVAT)($price);
    }

    /**
     * Adds multiple prices together.
     *
     * @param \MiBo\Prices\Contracts\PriceInterface $addend The first price to add.
     * @param \MiBo\Prices\Contracts\PriceInterface ...$addends The rest of the prices to add.
     *
     * @return array{0: float, 1: \MiBo\VAT\VAT}
     */
    public static function add(PriceInterface $addend, PriceInterface ...$addends): array
    {
        $vat      = $addend->getVAT();
        $combined = $addend->getVAT()->isCombined();

        foreach ($addends as $subAddend) {
            // Converting the Price into same currency.
            if (!$subAddend->getUnit()->is($addend->getUnit())) {
                $subAddend->convertToUnit($addend->getUnit());
            }

            // The main addend has the same VAT as the sub addend.
            if ($vat->is($subAddend->getVAT()) && !$combined) {
                $addend->getNumericalValue()->add($subAddend->getNumericalValue());

                continue;
            }

            // Adding two different VAT rates together, however none of them is combined.
            if (!$combined && $subAddend->getVAT()->isNotCombined()) {
                $addend->getNumericalValue()->add($subAddend->getNumericalValue());

                $combined = true;
                $vat      = VAT::get(
                    $vat->getCountryCode(),
                    VATRate::COMBINED,
                    CombinedTaxonomy::get(),
                    $vat->getDate()
                );

                continue;
            }

            // Adding a specific VAT rate to a combined one.
            if ($combined && $subAddend->getVAT()->isNotCombined()) {
                $addend->getNumericalValue()->add($subAddend->getNumericalValue());
            }
        }

        return [
            $addend->getNumericalValue()->getValue($addend->getUnit()->getMinorUnitRate() ?? 0),
            $vat,
        ];
    }

    /**
     * @param \Closure(\MiBo\Prices\Contracts\PriceInterface): (int|float)|null $closure
     *
     * @return void
     */
    final public static function setValueOfVATClosure(?Closure $closure): void
    {
        self::$getValueOfVAT = $closure;
    }

    /**
     * @param PriceCalculatorHelper $calculatorHelper
     *
     * @return void
     */
    final public static function setCalculatorHelper(PriceCalculatorHelper $calculatorHelper): void
    {
        self::$calculatorHelper = $calculatorHelper;
    }

    /**
     * @param \MiBo\Prices\Contracts\PriceComparer $comparerHelper
     *
     * @return void
     */
    public static function setComparerHelper(PriceComparer $comparerHelper): void
    {
        self::$comparerHelper = $comparerHelper;
    }

    /**
     * @since 2.0
     *
     * @param \MiBo\VAT\Manager $manager
     *
     * @return void
     */
    final public static function setVATManager(Manager $manager): void
    {
        self::$vatManager = $manager;
    }

    /**
     * @since 2.0
     *
     * @return \MiBo\VAT\Manager
     */
    final public static function getVATManager(): Manager
    {
        if (self::$vatManager === null) {
            throw new ValueError();
        }

        return self::$vatManager;
    }

    /**
     * @param string $name
     * @param array<int, mixed> $arguments
     *
     * @return array<string, int|float>|bool|object|float|int|null
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        $helper = [
            'round',
            'ceil',
            'floor',
        ];

        if (in_array($name, $helper)) {
            if (self::$calculatorHelper === null) {
                throw new DomainException('The PriceCalculatorHelper is not set.');
            }

            return self::$calculatorHelper->$name(...$arguments);
        }

        $helper = [
            'checkThat',
            'isLessThan',
            'isNotLessThan',
            'isLessThanOrEqual',
            'isNotLessThanOrEqual',
            'isGreaterThan',
            'isNotGreaterThan',
            'isGreaterThanOrEqual',
            'isNotGreaterThanOrEqual',
            'isEqual',
            'isNotEqual',
            'isBetween',
            'isNotBetween',
            'isBetweenOrEqual',
            'isNotBetweenOrEqual',
            'isInteger',
            'isNotInteger',
            'isFloat',
            'isNotFloat',
            'isEvent',
            'isNotEvent',
            'isOdd',
            'isNotOdd',
            'hasSameValueAs',
            'hasNotSameValueAs',
            'is',
            'isNot',
            'hasSameValueWithVATAs',
            'hasNotSameValueWithVATAs',
        ];

        if (in_array($name, $helper)) {
            if (self::$comparerHelper === null) {
                throw new DomainException('The PriceComparer is not set.');
            }

            return self::$comparerHelper->$name(...$arguments);
        }

        throw new BadMethodCallException('Method ' . $name . ' does not exist.');
    }
}
