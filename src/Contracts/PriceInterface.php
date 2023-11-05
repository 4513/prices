<?php

declare(strict_types=1);

namespace MiBo\Prices\Contracts;

use DateTimeInterface;
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
     *  The Price might be a group of prices with different VAT rates. This method adds or sets the nested
     * price. If the price already exists, it is added to the existing one.
     *
     * @param string $category VAT (product) category.
     * @param \MiBo\Prices\Contracts\PriceInterface $price
     *
     * @return void
     */
    public function setNestedPrice(string $category, PriceInterface $price): void;

    /**
     * Retrieves the nested price by the product category.
     *
     * @param string $category
     *
     * @return \MiBo\Prices\Contracts\PriceInterface|null
     */
    public function getNestedPrice(string $category): ?PriceInterface;

    /**
     * All nested prices.
     *
     * @return array<string, \MiBo\Prices\Contracts\PriceInterface>
     */
    public function getNestedPrices(): array;

    /**
     * The date for which the price is valid.
     *
     * @since 2.0
     *
     * @return \DateTimeInterface
     */
    public function getDateTime(): DateTimeInterface;

    /**
     * Changes the Price (normally the VAT) for the given country code.
     *
     * @param string $countryCode
     *
     * @return static
     */
    public function forCountry(string $countryCode): static;
}
