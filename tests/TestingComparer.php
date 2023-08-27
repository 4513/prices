<?php

declare(strict_types=1);

namespace MiBo\Prices\Tests;

use MiBo\Prices\Contracts\PriceComparer;
use MiBo\Prices\Contracts\PriceInterface;
use MiBo\Properties\Contracts\NumericalProperty;

/**
 * Class TestingComparer
 *
 * @package MiBo\Prices\Tests
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 1.2
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
class TestingComparer implements PriceComparer
{
    private PriceInterface $price;

    /**
     * @return \MiBo\Prices\Contracts\PriceInterface
     */
    private function getPrice(): PriceInterface
    {
        return $this->price;
    }

    public function checkThat(PriceInterface $price): static
    {
        $this->price = $price;

        return $this;
    }

    private function priceToValue(PriceInterface|float|int $price): float|int
    {
        if ($price instanceof PriceInterface) {
            return $price->convertToUnit($this->getPrice()->getUnit())->getValue();
        }

        return $price;
    }

    public function isLessThan(float|int|PriceInterface $price): bool
    {
        return $this->price->getValue() < $this->priceToValue($price);
    }

    public function isNotLessThan(float|int|PriceInterface $price): bool
    {
        return !$this->isLessThan($price);
    }

    public function isLessThanOrEqualTo(float|int|PriceInterface $price): bool
    {
        return $this->isLessThan($price) || $this->isEqualTo($price);
    }

    public function isNotLessThanOrEqualTo(float|int|PriceInterface $price): bool
    {
        return !$this->isLessThanOrEqualTo($price);
    }

    public function isGreaterThan(float|int|PriceInterface $price): bool
    {
        return $this->isNotLessThanOrEqualTo($price);
    }

    public function isNotGreaterThan(float|int|PriceInterface $price): bool
    {
        return $this->isLessThanOrEqualTo($price);
    }

    public function isGreaterThanOrEqualTo(float|int|PriceInterface $price): bool
    {
        return $this->isNotLessThan($price);
    }

    public function isNotGreaterThanOrEqualTo(float|int|PriceInterface $price): bool
    {
        return $this->isLessThan($price);
    }

    public function isEqualTo(float|int|PriceInterface $price): bool
    {
        return (float) $this->price->getValue() === (float) $this->priceToValue($price);
    }

    public function isNotEqualTo(float|int|PriceInterface $price): bool
    {
        return !$this->isEqualTo($price);
    }

    public function isBetween(float|int|PriceInterface $first, float|int|PriceInterface $second): bool
    {
        return ($this->isGreaterThan($first) && $this->isLessThan($second)) ||
            $this->isLessThan($first) || $this->isGreaterThan($second);
    }

    public function isNotBetween(float|int|PriceInterface $first, float|int|PriceInterface $second): bool
    {
        return !$this->isBetween($first, $second);
    }

    public function isBetweenOrEqualTo(
        float|int|PriceInterface $first,
        float|int|PriceInterface $second
    ): bool
    {
        return $this->isBetween($first, $second) || $this->isEqualTo($first) || $this->isEqualTo($second);
    }

    public function isNotBetweenOrEqualTo(
        float|int|PriceInterface $first,
        float|int|PriceInterface $second
    ): bool
    {
        return !$this->isBetweenOrEqualTo($first, $second);
    }

    public function isInteger(): bool
    {
        return $this->price->getValue() === (int) $this->price->getValue();
    }

    public function isNotInteger(): bool
    {
        return !$this->isInteger();
    }

    public function isFloat(): bool
    {
        return $this->price->getValue() === (float) $this->price->getValue();
    }

    public function isNotFloat(): bool
    {
        return !$this->isFloat();
    }

    public function isEven(): bool
    {
        return $this->isInteger() && $this->price->getValue() % 2 === 0;
    }

    public function isNotEven(): bool
    {
        return !$this->isEven();
    }

    public function isOdd(): bool
    {
        return $this->isInteger() && $this->price->getValue() % 2 !== 0;
    }

    public function isNotOdd(): bool
    {
        return !$this->isOdd();
    }

    public function hasSameValueAs(NumericalProperty|float|int $price): bool
    {
        return $this->price->getValue() === ($price instanceof NumericalProperty ? $price->getValue() : $price);
    }

    public function hasNotSameValueAs(NumericalProperty|float|int $price): bool
    {
        return !$this->hasSameValueAs($price);
    }

    public function hasSameValueWithVATAs(NumericalProperty|float|int $price): bool
    {
        $price = $price instanceof PriceInterface ?
            $price->convertToUnit($this->getPrice()->getUnit())->getValueWithVAT() :
            $price;

        return $this->price->getValueWithVAT() === ($price instanceof NumericalProperty ?
                $price->getValue() :
                $price
            );
    }

    public function hasNotSameValueWithVATAs(NumericalProperty|float|int $price): bool
    {
        return !$this->hasSameValueWithVATAs($price);
    }

    public function isWithVATEqualTo(float|int|PriceInterface $price): bool
    {
        return $this->price->getValueWithVAT() === ($price instanceof PriceInterface ?
            $price->convertToUnit($this->getPrice()->getUnit())->getValueWithVAT() :
            $price);
    }

    public function isWithVATNotEqualTo(float|int|PriceInterface $price): bool
    {
        return !$this->isWithVATEqualTo($price);
    }

    public function is(PriceInterface $price, bool $strict = false): bool
    {
        return false;
    }

    public function isNot(PriceInterface $price, bool $strict = false): bool
    {
        return false;
    }
}
