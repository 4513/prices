<?php

declare(strict_types=1);

namespace MiBo\Prices\Calculators;

use MiBo\Prices\Contracts\PriceInterface;
use MiBo\VAT\Enums\VATRate;
use MiBo\VAT\Resolvers\ProxyResolver;
use MiBo\VAT\VAT;

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

        return $price->getNumericalValue()->getValue(
                $price->getUnit()->getMinorUnitRate() ?? 0
            ) * ProxyResolver::getPercentageOf(
                $price->getVAT(),
                method_exists($price, 'getDateTime') ? $price->getDateTime() : null
            );
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
                    $addend->getVAT()->getCountryCode(),
                    VATRate::COMBINED
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
}
