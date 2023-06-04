<?php

declare(strict_types = 1);

namespace MiBo\Prices\Calculators;

use MiBo\Prices\Contracts\PriceInterface;
use MiBo\Prices\Price;
use MiBo\Properties\Contracts\NumericalProperty as ContractNumericalProperty;
use MiBo\VAT\Enums\VATRate;
use MiBo\VAT\VAT;
use ValueError;

/**
 * Class PriceCalc
 *
 * @package MiBo\Prices\Calculators
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since x.x
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
class PriceCalc
{
    public static function getValueOfVAT(PriceInterface $price): int|float
    {

    }

    /**
     * @param \MiBo\Prices\Contracts\PriceInterface $addend
     * @param \MiBo\Prices\Contracts\PriceInterface ...$addends
     *
     * @return array{0: \MiBo\Prices\Contracts\PriceInterface, 1: \MiBo\VAT\VAT}
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

            // Addend has combined VAT. Using it instead of the current one.
            if ($subAddend->getVAT()->isCombined()) {
                $temporarySelf = clone $addend;

                $addend->getNumericalValue()
                    ->multiply(0)
                    ->add($subAddend->getNumericalValue());
                $addend->add($temporarySelf);

                $combined = true;

                continue;
            }

            // Addend has the same VAT. We can add these two prices together.
            if ($subAddend->getVAT()->is($addend->getVAT()) && !$combined) {
                $addend->getNumericalValue()->add($subAddend->getNumericalValue());

                continue;
            }

            // Two combined VATs. Merging their nested prices.
            if ($subAddend->getVAT()->is($addend->getVAT())) {
                foreach ($subAddend->getNestedPrices() as $category => $price) {
                    $addend->setNestedPrice($category, $price);
                }

                continue;
            }

            // Addend has a different VAT rate. Transforming VAT rate into Combined.
            $addend->setNestedPrice($subAddend->getVAT()->getCategory() ?? "", clone $subAddend);

            if ($addend->getVAT()->isCombined()) {
                continue;
            }

            $addend->setNestedPrice($addend->getVAT()->getCategory() ?? "", clone $addend);

            $vat = VAT::get(
                $addend->getVAT()->getCountryCode(),
                VATRate::COMBINED,
                $addend->getVAT()->getCategory(),
            );
        }

        return [
            $addend,
            $vat,
        ];
    }

    public static function subtract(PriceInterface $minuend, PriceInterface ...$subtrahends): PriceInterface
    {
        $vat      = $minuend->getVAT();
        $combined = $vat->isCombined();

        foreach ($subtrahends as $subtrahend) {
            if (!$combined && ($vat->is($subtrahend->getVAT()) || $subtrahend->getVAT()->isAny())) {
                $minuend->getNumericalValue()->subtract($subtrahend->getNumericalValue());

                if ($minuend->getValue() < 0) {
                    // @phpcs:ignore
                    throw new ValueError("Subtracting too much! Cannot subtract a price that is higher than the minuend.");
                }

                continue;
            }

            if ($combined && $vat->isCombined()) {
                $minuend = self::subtract($minuend, ...$subtrahend->getNestedPrices());

                continue;
            }

            if ($subtrahend->getVAT()->isAny()) {
                $success = false;

                foreach ($subtrahend->getNestedPrices() as $category => $price) {
                    try {
                        $minuend->getNestedPrice($category)->subtract($subtrahend);

                        $success = true;
                    } catch (ValueError) {
                        $subtrahend->getNumericalValue()->subtract($price->getNumericalValue());
                        $minuend->getNestedPrice($category)->multiply(0);
                    }
                }

                if (!$success) {
                    // @phpcs:ignore
                    throw new ValueError("Subtracting too much! Cannot subtract a price that is higher than the minuend.");
                }

                $minuend->getNumericalValue()->subtract($subtrahend->getNumericalValue());

                continue;
            }

            if ($minuend->getNestedPrice($subtrahend->getVAT()->getCategory()) === null) {
                // @phpcs:ignore
                throw new ValueError("Subtracting from nothing! Cannot subtract from a price that does not have a price with the same VAT rate.");
            }

            $minuend->getNestedPrice($subtrahend->getVAT()->getCategory())->subtract($subtrahend);
            $minuend->getNumericalValue()->subtract($subtrahend->getNumericalValue());
        }

        return $minuend;
    }

    public static function merge(bool $add = true, PriceInterface ...$prices): PriceInterface
    {

    }
}
