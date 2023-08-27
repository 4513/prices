<?php

declare(strict_types=1);

namespace MiBo\Prices\Contracts;

/**
 * Interface PriceCalculatorHelper
 *
 * @package MiBo\Prices\Contracts
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 1.2
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
interface PriceCalculatorHelper
{
    /**
     * Rounds the price.
     *
     * @param \MiBo\Prices\Contracts\PriceInterface $price Price to round.
     * @param int $precision Precision of the rounding.
     * @param int<1, 4> $mode Rounding mode.
     *
     * @return array<string, int|float> Rounded prices for each VAT category.
     */
    public function round(PriceInterface $price, int $precision = 0, int $mode = PHP_ROUND_HALF_UP): array;

    /**
     * Rounds the price up.
     *
     * @param \MiBo\Prices\Contracts\PriceInterface $price Price to round.
     * @param int $precision Precision of the rounding.
     *
     * @return array<string, int|float> Rounded prices for each VAT category.
     */
    public function ceil(PriceInterface $price, int $precision = 0): array;

    /**
     * Rounds the price down.
     *
     * @param \MiBo\Prices\Contracts\PriceInterface $price Price to round.
     * @param int $precision Precision of the rounding.
     *
     * @return array<string, int|float> Rounded prices for each VAT category.
     */
    public function floor(PriceInterface $price, int $precision = 0): array;
}
