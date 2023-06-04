<?php

declare(strict_types = 1);

namespace MiBo\Prices\Calculators;

use MiBo\Prices\Contracts\PriceInterface;
use MiBo\Prices\Price;
use MiBo\Properties\Contracts\NumericalProperty as ContractNumericalProperty;
use MiBo\VAT\Enums\VATRate;
use MiBo\VAT\VAT;

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
        $vat = $addend->getVAT();

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

                continue;
            }

            // Addend has the same VAT. We can add these two prices together.
            if ($subAddend->getVAT()->is($addend->getVAT())) {
                $addend->getNumericalValue()->add($subAddend->getNumericalValue());

                continue;
            }

            // Addend has a different VAT rate. Transforming VAT rate into Combined.
            $addend->setNestedPrice($subAddend->getVAT()->getCategory() ?? "", clone $subAddend);

            if ($addend->getVAT()->isCombined()) {
                continue;
            }

            $addend->setNestedPrice($addend->getVAT()->getCategory() ?? "", clone $addend);
            $addend->getNumericalValue()->add($subAddend->getNumericalValue());

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

    public static function merge(bool $add = true, PriceInterface ...$prices): PriceInterface
    {

    }
}
