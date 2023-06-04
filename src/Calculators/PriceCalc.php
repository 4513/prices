<?php

declare(strict_types=1);

namespace MiBo\Prices\Calculators;

use MiBo\Prices\Contracts\PriceInterface;
use MiBo\VAT\Enums\VATRate;
use MiBo\VAT\Resolvers\ProxyResolver;
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
 */
class PriceCalc
{
    /**
     * Returns the value of the VAT of the amount.
     *
     * @param \MiBo\Prices\Contracts\PriceInterface $price
     *
     * @return int|float
     */
    public static function getValueOfVAT(PriceInterface $price): int|float
    {
        if ($price->getVAT()->isNone() || $price->getVAT()->isCombined() || $price->getVAT()->isAny()) {
            return 0;
        }

        return $price->getValue() * ProxyResolver::getPercentageOf($price->getVAT());
    }

    /**
     * Adds multiple prices together.
     *
     * @param \MiBo\Prices\Contracts\PriceInterface $addend The first price to add.
     * @param \MiBo\Prices\Contracts\PriceInterface ...$addends The rest of the prices to add.
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

            $newAddend = clone $addend;

            $addend->getNumericalValue()->multiply(0);
            $addend->setNestedPrice($addend->getVAT()->getCategory() ?? "", $newAddend);

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

    /**
     * Subtracts multiple prices from the minuend.
     *
     * @param \MiBo\Prices\Contracts\PriceInterface $minuend The price to subtract from.
     * @param \MiBo\Prices\Contracts\PriceInterface ...$subtrahends The rest of the prices to subtract.
     *
     * @return \MiBo\Prices\Contracts\PriceInterface
     */
    public static function subtract(PriceInterface $minuend, PriceInterface ...$subtrahends): PriceInterface
    {
        $vat      = $minuend->getVAT();
        $combined = $vat->isCombined();

        foreach ($subtrahends as $subtrahend) {
            // Converting the Price into same currency.
            if (!$subtrahend->getUnit()->is($minuend->getUnit())) {
                $subtrahend->convertToUnit($minuend->getUnit());
            }

            // Subtrahend has the same VAT or 'any' VAT, thus we can subtract it directly.
            if (!$combined && ($vat->is($subtrahend->getVAT()) || $subtrahend->getVAT()->isAny())) {
                $minuend->getNumericalValue()->subtract($subtrahend->getNumericalValue());

                // Subtrahend is bigger and so we cannot continue.
                if ($minuend->getValue() < 0) {
                    // @phpcs:ignore
                    throw new ValueError("Subtracting too much! Cannot subtract a price that is higher than the minuend.");
                }

                continue;
            }

            // Subtrahend has combined VAT. We can loop all of its prices and subtract them one by one.
            if ($combined && $subtrahend->getVAT()->isCombined()) {
                $minuend = self::subtract($minuend, ...$subtrahend->getNestedPrices());

                continue;
            }

            // Subtrahend has any VAT. We loop its prices and try to subtract from all possible prices.
            if ($subtrahend->getVAT()->isAny()) {
                $success = false;

                foreach ($subtrahend->getNestedPrices() as $category => $price) {
                    try {
                        $minuend->getNestedPrice($category)->subtract($subtrahend);

                        $success = true;
                    } catch (ValueError) {
                        //  The subtrahend is bigger, but that is not a problem for now. We reset the minuend
                        // and subtract the subtrahend by the minuend's value, and then we continue again.
                        $subtrahend->getNumericalValue()->subtract($price->getNumericalValue());
                        $minuend->getNestedPrice($category)->multiply(0);
                    }
                }

                // The subtrahend has been bigger.
                if (!$success) {
                    // @phpcs:ignore
                    throw new ValueError("Subtracting too much! Cannot subtract a price that is higher than the minuend.");
                }

                $minuend->getNumericalValue()->subtract($subtrahend->getNumericalValue());

                continue;
            }

            // Subtrahend has a different VAT rate.
            if ($minuend->getNestedPrice($subtrahend->getVAT()->getCategory()) === null) {
                // @phpcs:ignore
                throw new ValueError("Subtracting from nothing! Cannot subtract from a price that does not have a price with the same VAT rate.");
            }

            // Minuend has combined VAT while the subtrahend has a specific one. We subtract only the correct price.
            $minuend->getNestedPrice($subtrahend->getVAT()->getCategory())->subtract($subtrahend);
            $minuend->getNumericalValue()->subtract($subtrahend->getNumericalValue());
        }

        return $minuend;
    }
}
