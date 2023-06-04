<?php

declare(strict_types=1);

namespace MiBo\Prices\Contracts;

use MiBo\Prices\Price;
use MiBo\Properties\Contracts\NumericalProperty;
use MiBo\VAT\VAT;

/**
 * Interface PriceInterface
 *
 * @package MiBo\Prices\Contracts
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 0.1
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
interface PriceInterface extends NumericalProperty
{
    /**
     * VAT of the Price.
     *
     * @return \MiBo\VAT\VAT
     */
    public function getVAT(): VAT;

    /**
     * Total value extended by the VAT size.
     *
     * @return int|float
     */
    public function getValueWithVAT(): int|float;

    /**
     * The VAT size of total value.
     *
     * *Returned value is a price, not the rate.*
     *
     * @return int|float
     */
    public function getValueOfVAT(): int|float;

    /**
     * @param string $category
     * @param \MiBo\Prices\Price $price
     *
     * @return void
     */
    public function setNestedPrice(string $category, Price $price): void;
}
