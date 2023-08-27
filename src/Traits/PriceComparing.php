<?php

declare(strict_types=1);

namespace MiBo\Prices\Traits;

use MiBo\Prices\Calculators\PriceCalc;
use MiBo\Prices\Contracts\PriceInterface;
use MiBo\Properties\Contracts\ComparableProperty;
use MiBo\Properties\Contracts\NumericalComparableProperty;
use ValueError;

/**
 * Trait PriceComaring
 *
 * @package MiBo\Prices\Traits
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 1.2
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
trait PriceComparing
{
    /**
     * @inheritDoc
     */
    public function isLessThan(float|int|NumericalComparableProperty $property): bool
    {
        if ($property instanceof NumericalComparableProperty && !$property instanceof PriceInterface) {
            throw new ValueError('Property must be instance of PriceInterface!');
        }

        return PriceCalc::checkThat($this)->isLessThan($property);
    }

    /**
     * @inheritDoc
     */
    public function isNotLessThan(float|int|NumericalComparableProperty $property): bool
    {
        if ($property instanceof NumericalComparableProperty && !$property instanceof PriceInterface) {
            throw new ValueError('Property must be instance of PriceInterface!');
        }

        return PriceCalc::checkThat($this)->isNotLessThan($property);
    }

    /**
     * @inheritDoc
     */
    public function isLessThanOrEqualTo(float|int|NumericalComparableProperty $property): bool
    {
        if ($property instanceof NumericalComparableProperty && !$property instanceof PriceInterface) {
            throw new ValueError('Property must be instance of PriceInterface!');
        }

        return PriceCalc::checkThat($this)->isLessThanOrEqualTo($property);
    }

    /**
     * @inheritDoc
     */
    public function isNotLessThanOrEqualTo(float|int|NumericalComparableProperty $property): bool
    {
        if ($property instanceof NumericalComparableProperty && !$property instanceof PriceInterface) {
            throw new ValueError('Property must be instance of PriceInterface!');
        }

        return PriceCalc::checkThat($this)->isNotLessThanOrEqualTo($property);
    }

    /**
     * @inheritDoc
     */
    public function isGreaterThan(float|int|NumericalComparableProperty $property): bool
    {
        if ($property instanceof NumericalComparableProperty && !$property instanceof PriceInterface) {
            throw new ValueError('Property must be instance of PriceInterface!');
        }

        return PriceCalc::checkThat($this)->isGreaterThan($property);
    }

    /**
     * @inheritDoc
     */
    public function isNotGreaterThan(float|int|NumericalComparableProperty $property): bool
    {
        if ($property instanceof NumericalComparableProperty && !$property instanceof PriceInterface) {
            throw new ValueError('Property must be instance of PriceInterface!');
        }

        return PriceCalc::checkThat($this)->isNotGreaterThan($property);
    }

    /**
     * @inheritDoc
     */
    public function isGreaterThanOrEqualTo(float|int|NumericalComparableProperty $property): bool
    {
        if ($property instanceof NumericalComparableProperty && !$property instanceof PriceInterface) {
            throw new ValueError('Property must be instance of PriceInterface!');
        }

        return PriceCalc::checkThat($this)->isGreaterThanOrEqualTo($property);
    }

    /**
     * @inheritDoc
     */
    public function isNotGreaterThanOrEqualTo(float|int|NumericalComparableProperty $property): bool
    {
        if ($property instanceof NumericalComparableProperty && !$property instanceof PriceInterface) {
            throw new ValueError('Property must be instance of PriceInterface!');
        }

        return PriceCalc::checkThat($this)->isNotGreaterThanOrEqualTo($property);
    }

    /**
     * @inheritDoc
     */
    public function isEqualTo(float|int|NumericalComparableProperty $property): bool
    {
        if ($property instanceof NumericalComparableProperty && !$property instanceof PriceInterface) {
            throw new ValueError('Property must be instance of PriceInterface!');
        }

        return PriceCalc::checkThat($this)->isEqualTo($property);
    }

    /**
     * @inheritDoc
     */
    public function isNotEqualTo(float|int|NumericalComparableProperty $property): bool
    {
        if ($property instanceof NumericalComparableProperty && !$property instanceof PriceInterface) {
            throw new ValueError('Property must be instance of PriceInterface!');
        }

        return PriceCalc::checkThat($this)->isNotEqualTo($property);
    }

    /**
     * @inheritDoc
     */
    public function isBetween(
        float|int|NumericalComparableProperty $first,
        float|int|NumericalComparableProperty $second
    ): bool
    {
        if (($first instanceof NumericalComparableProperty && !$first instanceof PriceInterface)
            || ($second instanceof NumericalComparableProperty && !$second instanceof PriceInterface)
        ) {
            throw new ValueError('Property must be instance of PriceInterface!');
        }

        return PriceCalc::checkThat($this)->isBetween($first, $second);
    }

    /**
     * @inheritDoc
     */
    public function isNotBetween(
        float|int|NumericalComparableProperty $first,
        float|int|NumericalComparableProperty $second
    ): bool
    {
        if (($first instanceof NumericalComparableProperty && !$first instanceof PriceInterface)
            || ($second instanceof NumericalComparableProperty && !$second instanceof PriceInterface)
        ) {
            throw new ValueError('Property must be instance of PriceInterface!');
        }

        return PriceCalc::checkThat($this)->isNotBetween($first, $second);
    }

    /**
     * @inheritDoc
     */
    public function isBetweenOrEqualTo(
        float|int|NumericalComparableProperty $first,
        float|int|NumericalComparableProperty $second
    ): bool
    {
        if (($first instanceof NumericalComparableProperty && !$first instanceof PriceInterface)
            || ($second instanceof NumericalComparableProperty && !$second instanceof PriceInterface)
        ) {
            throw new ValueError('Property must be instance of PriceInterface!');
        }

        return PriceCalc::checkThat($this)->isBetweenOrEqualTo($first, $second);
    }

    /**
     * @inheritDoc
     */
    public function isNotBetweenOrEqualTo(
        float|int|NumericalComparableProperty $first,
        float|int|NumericalComparableProperty $second
    ): bool
    {
        if (($first instanceof NumericalComparableProperty && !$first instanceof PriceInterface)
            || ($second instanceof NumericalComparableProperty && !$second instanceof PriceInterface)
        ) {
            throw new ValueError('Property must be instance of PriceInterface!');
        }

        return PriceCalc::checkThat($this)->isNotBetweenOrEqualTo($first, $second);
    }

    /**
     * @inheritDoc
     */
    public function isInteger(): bool
    {
        return PriceCalc::checkThat($this)->isInteger();
    }

    /**
     * @inheritDoc
     */
    public function isNotInteger(): bool
    {
        return PriceCalc::checkThat($this)->isNotInteger();
    }

    /**
     * @inheritDoc
     */
    public function isFloat(): bool
    {
        return PriceCalc::checkThat($this)->isFloat();
    }

    /**
     * @inheritDoc
     */
    public function isNotFloat(): bool
    {
        return PriceCalc::checkThat($this)->isNotFloat();
    }

    /**
     * @inheritDoc
     */
    public function isEven(): bool
    {
        return PriceCalc::checkThat($this)->isEven();
    }

    /**
     * @inheritDoc
     */
    public function isNotEven(): bool
    {
        return PriceCalc::checkThat($this)->isNotEven();
    }

    /**
     * @inheritDoc
     */
    public function isOdd(): bool
    {
        return PriceCalc::checkThat($this)->isOdd();
    }

    /**
     * @inheritDoc
     */
    public function isNotOdd(): bool
    {
        return PriceCalc::checkThat($this)->isNotOdd();
    }

    /**
     * @inheritDoc
     *
     * @phpcs:ignore Generic.Files.LineLength.TooLong
     * @param (\MiBo\Properties\Contracts\NumericalComparableProperty&\MiBo\Properties\Contracts\NumericalProperty)|float|int $property
     */
    public function hasSameValueAs(float|int|NumericalComparableProperty $property): bool
    {
        return PriceCalc::checkThat($this)->hasSameValueAs($property);
    }

    /**
     * @inheritDoc
     *
     * @phpcs:ignore Generic.Files.LineLength.TooLong
     * @param (\MiBo\Properties\Contracts\NumericalComparableProperty&\MiBo\Properties\Contracts\NumericalProperty)|float|int $property
     */
    public function hasNotSameValueAs(float|int|NumericalComparableProperty $property): bool
    {
        return PriceCalc::checkThat($this)->hasNotSameValueAs($property);
    }

    /**
     * @inheritDoc
     */
    public function is(float|ComparableProperty|int $property, bool $strict = false): bool
    {
        if ($property instanceof ComparableProperty && !$property instanceof PriceInterface) {
            throw new ValueError('Property must be instance of PriceInterface!');
        }

        return $property instanceof PriceInterface && PriceCalc::checkThat($this)->is($property, $strict);
    }

    /**
     * @inheritDoc
     */
    public function isNot(float|ComparableProperty|int $property, bool $strict = false): bool
    {
        if ($property instanceof ComparableProperty && !$property instanceof PriceInterface) {
            throw new ValueError('Property must be instance of PriceInterface!');
        }

        return $property instanceof PriceInterface && PriceCalc::checkThat($this)->isNot($property, $strict);
    }

    /**
     * Checks that the value with VAT is same as the value of given property.
     *
     * @param (\MiBo\Properties\Contracts\ComparableProperty&\MiBo\Properties\NumericalProperty)|float|int $property
     *
     * @return bool
     */
    public function hasSameValueWithVATAs(ComparableProperty|float|int $property): bool
    {
        return PriceCalc::checkThat($this)->hasSameValueWithVATAs($property);
    }

    /**
     * Checks that the value with VAT is not same as the value of given property.
     *
     * @param (\MiBo\Properties\Contracts\ComparableProperty&\MiBo\Properties\NumericalProperty)|float|int $property
     *
     * @return bool
     */
    public function hasNotSameValueWithVATAs(ComparableProperty|float|int $property): bool
    {
        return PriceCalc::checkThat($this)->hasNotSameValueWithVATAs($property);
    }

    /**
     * Checks that the value with VAT is same as the value of given property.
     *
     * **This method converts the property if not same unit and VAT!**
     *
     * @param \MiBo\Prices\Contracts\PriceInterface|float|int $property
     *
     * @return bool
     */
    public function isWithVATEqualTo(PriceInterface|float|int $property): bool
    {
        return PriceCalc::checkThat($this)->isWithVATEqualTo($property);
    }

    /**
     * Checks that the value with VAT is same as the value of given property.
     *
     * **This method converts the property if not same unit and VAT!**
     *
     * @param \MiBo\Prices\Contracts\PriceInterface|float|int $property
     *
     * @return bool
     */
    public function isWithVATNotEqualTo(PriceInterface|float|int $property): bool
    {
        return PriceCalc::checkThat($this)->isWithVATNotEqualTo($property);
    }
}
