<?php

declare(strict_types=1);

namespace MiBo\Prices\Contracts;

use MiBo\Prices\Price;
use MiBo\Properties\Contracts\NumericalProperty;

/**
 * Class PriceComparer
 *
 * @package MiBo\Prices\Contracts
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 1.2
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
interface PriceComparer
{
    public function checkThat(PriceInterface $price): static;

    public function isLessThan(PriceInterface|int|float $price): bool;

    public function isNotLessThan(PriceInterface|int|float $price): bool;

    public function isLessThanOrEqualTo(PriceInterface|int|float $price): bool;

    public function isNotLessThanOrEqualTo(PriceInterface|int|float $price): bool;

    public function isGreaterThan(PriceInterface|int|float $price): bool;

    public function isNotGreaterThan(PriceInterface|int|float $price): bool;

    public function isGreaterThanOrEqualTo(PriceInterface|int|float $price): bool;

    public function isNotGreaterThanOrEqualTo(PriceInterface|int|float $price): bool;

    public function isEqualTo(PriceInterface|int|float $price): bool;

    public function isNotEqualTo(PriceInterface|int|float $price): bool;

    public function isBetween(PriceInterface|int|float $first, PriceInterface|int|float $second): bool;

    public function isNotBetween(PriceInterface|int|float $first, PriceInterface|int|float $second): bool;

    public function isBetweenOrEqualTo(PriceInterface|int|float $first, PriceInterface|int|float $second): bool;

    public function isNotBetweenOrEqualTo(PriceInterface|int|float $first, PriceInterface|int|float $second): bool;

    public function isInteger(): bool;

    public function isNotInteger(): bool;

    public function isFloat(): bool;

    public function isNotFloat(): bool;

    public function isEven(): bool;

    public function isNotEven(): bool;

    public function isOdd(): bool;

    public function isNotOdd(): bool;

    public function hasSameValueAs(NumericalProperty|int|float $price): bool;

    public function hasNotSameValueAs(NumericalProperty|int|float $price): bool;

    public function hasSameValueWithVATAs(NumericalProperty|int|float $price): bool;

    public function hasNotSameValueWithVATAs(NumericalProperty|int|float $price): bool;

    public function isWithVATEqualTo(PriceInterface|int|float $price): bool;

    public function isWithVATNotEqualTo(PriceInterface|int|float $price): bool;

    public function is(PriceInterface $price, bool $strict = false): bool;

    public function isNot(PriceInterface $price, bool $strict = false): bool;
}
