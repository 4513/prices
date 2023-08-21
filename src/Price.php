<?php

declare(strict_types=1);

namespace MiBo\Prices;

use DateTime;
use MiBo\Prices\Calculators\PriceCalc;
use MiBo\Prices\Contracts\PriceInterface;
use MiBo\Prices\Traits\PriceHelper;
use MiBo\Properties\Contracts\ComparableProperty;
use MiBo\Properties\Contracts\NumericalComparableProperty;
use MiBo\Properties\Contracts\NumericalProperty as ContractNumericalProperty;
use MiBo\Properties\Contracts\Unit;
use MiBo\Properties\NumericalProperty;
use MiBo\Properties\Value;
use MiBo\VAT\Enums\VATRate;
use MiBo\VAT\Resolvers\ProxyResolver;
use MiBo\VAT\VAT;
use ValueError;
use function is_float;
use function is_int;

/**
 * Class Price
 *
 * @package MiBo\Prices
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 0.1
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
class Price extends NumericalProperty implements PriceInterface
{
    use PriceHelper;

    protected VAT $vat;

    /** @var \MiBo\Prices\Units\Price\Currency */
    protected Unit $unit;

    protected ?DateTime $time;

    private Value $initialValue;

    private VAT $initialVAT;

    /**
     * @param float|\MiBo\Properties\Value|int $value
     * @param \MiBo\Prices\Units\Price\Currency $unit
     * @param \MiBo\VAT\VAT|null $vat
     * @param \DateTime|null $time
     */
    public function __construct(float|Value|int $value, Unit $unit, ?VAT $vat = null, ?DateTime $time = null)
    {
        $this->vat  = $vat === null ? VAT::get("", VATRate::NONE) : $vat;
        $this->time = $time;

        $value = $value instanceof Value ?
            $value :
            new Value($value, $unit->getMinorUnitRate() ?? 0, 0);

        parent::__construct($value, $unit);

        $this->initialValue = (clone $this->getNumericalValue())->multiply(0);
        $this->initialVAT   = clone $this->getVAT();

        $this->prices[$this->getVAT()->getCategory() ?? ""] = clone $this;
    }

    /**
     * @inheritDoc
     */
    public function add(ContractNumericalProperty|float|int $value): static
    {
        // Adding incompatible types.
        if ($value instanceof ContractNumericalProperty && !$value instanceof Price) {
            throw new ValueError("Cannot add incompatible types together.");
        }

        // Adding float or int with no VAT specified is forbidden when having combined VAT.
        if (!$value instanceof Price && $this->getVAT()->isCombined()) {
            throw new ValueError("Cannot add a float or an integer to a combined VAT price! Specify VAT category.");
        }

        // Transforming float and int into Price.
        if (is_int($value) || is_float($value)) {
            $value = new self($value, $this->unit, $this->vat, $this->time);
        }

        if ($value->getVAT()->isCombined()) {
            $value->convertToUnit($this->getUnit());

            foreach ($value->getNestedPrices() as $nestedPrice) {
                $this->add($nestedPrice);
            }

            return $this;
        }

        $value->convertToUnit($this->getUnit());
        $this->setNestedPrice($value->getVAT()->getCategory() ?? "", $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function subtract(ContractNumericalProperty|float|int $value): static
    {
        // Adding float or int with no VAT specified is forbidden when having combined VAT.
        if (!$value instanceof Price && $this->getVAT()->isCombined()) {
            throw new ValueError(
                "Cannot subtract a float or an integer from a combined VAT price! Specify VAT category."
            );
        }

        if (!$value instanceof Price && $value instanceof ContractNumericalProperty) {
            throw new ValueError("Cannot subtract incompatible types together.");
        }

        if (!$value instanceof Price) {
            $value = new self($value, $this->unit, $this->vat, $this->time);
        }

        $value = (clone $value)->multiply(-1);

        $value->getValue();

        return $this->add($value);
    }

    protected function compute(): void
    {
        $this->getNumericalValue()->multiply(0)->add($this->initialValue);
        $this->vat = $this->initialVAT;

        [
            $copy,
            $vat,
        ] = PriceCalc::add($this, ...array_values($this->prices));

        $this->vat = $vat;
    }

    /**
     * @inheritDoc
     */
    public static function getQuantityClassName(): string
    {
        return Quantities\Price::class;
    }

    /**
     * @inheritDoc
     */
    public function getValue(): int|float
    {
        $this->compute();

        return $this->getNumericalValue()->getValue($this->unit->getMinorUnitRate() ?? 0);
    }

    /**
     * @inheritDoc
     */
    public function getBaseValue(): int|float
    {
        return $this->getValue();
    }

    /**
     * @inheritDoc
     */
    public function getVAT(): VAT
    {
        return $this->vat;
    }

    /**
     * @inheritDoc
     */
    public function getValueWithVAT(): int|float
    {
        $withoutVAT = $this->getValue();

        return $withoutVAT === 0 ? 0 : $withoutVAT + $this->getValueOfVAT();
    }

    /**
     * @inheritDoc
     */
    public function getValueOfVAT(): int|float
    {
        $value = 0;

        foreach ($this->prices as $price) {
            $value += PriceCalc::getValueOfVAT($price);
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function forCountry(string $countryCode): static
    {
        $this->vat = ProxyResolver::retrieveByCategory($this->vat->getCategory() ?? "", $countryCode);

        foreach ($this->prices as $price) {
            $price->forCountry($countryCode);
        }

        return $this;
    }

    /**
     * Datetime of the price.
     *
     * @return \DateTime|null
     */
    public function getDateTime(): ?DateTime
    {
        return $this->time;
    }

    /**
     * @inheritDoc
     *
     * @return \MiBo\Prices\Units\Price\Currency
     */
    public function getUnit(): Unit
    {
        /** @phpstan-var \MiBo\Prices\Units\Price\Currency */
        return parent::getUnit();
    }

    // @codeCoverageIgnoreStart

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function isLessThan(float|int|NumericalComparableProperty $property): bool
    {
        return parent::isLessThan($property);
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function isNotLessThan(float|int|NumericalComparableProperty $property): bool
    {
        return parent::isNotLessThan($property);
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function isLessThanOrEqualTo(float|int|NumericalComparableProperty $property): bool
    {
        return parent::isLessThanOrEqualTo($property);
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function isNotLessThanOrEqualTo(float|int|NumericalComparableProperty $property): bool
    {
        return parent::isNotLessThanOrEqualTo($property);
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function isGreaterThan(float|int|NumericalComparableProperty $property): bool
    {
        return parent::isGreaterThan($property);
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function isNotGreaterThan(float|int|NumericalComparableProperty $property): bool
    {
        return parent::isNotGreaterThan($property);
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function isGreaterThanOrEqualTo(float|int|NumericalComparableProperty $property): bool
    {
        return parent::isGreaterThanOrEqualTo($property);
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function isNotGreaterThanOrEqualTo(float|int|NumericalComparableProperty $property): bool
    {
        return parent::isNotGreaterThanOrEqualTo($property);
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function isEqualTo(float|int|NumericalComparableProperty $property): bool
    {
        if ($property instanceof self) {
            $property->forCountry($this->getVAT()->getCountryCode());
        }

        return parent::isEqualTo($property);
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function isNotEqualTo(float|int|NumericalComparableProperty $property): bool
    {
        return parent::isNotEqualTo($property);
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function isBetween(
        float|int|NumericalComparableProperty $first,
        float|int|NumericalComparableProperty $second
    ): bool
    {
        return parent::isBetween($first, $second);
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function isNotBetween(
        float|int|NumericalComparableProperty $first,
        float|int|NumericalComparableProperty $second
    ): bool
    {
        return parent::isNotBetween($first, $second);
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function isBetweenOrEqualTo(
        float|int|NumericalComparableProperty $first,
        float|int|NumericalComparableProperty $second
    ): bool
    {
        return parent::isBetweenOrEqualTo($first, $second);
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function isNotBetweenOrEqualTo(
        float|int|NumericalComparableProperty $first,
        float|int|NumericalComparableProperty $second
    ): bool
    {
        return parent::isNotBetweenOrEqualTo($first, $second);
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function isInteger(): bool
    {
        return parent::isInteger();
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function isNotInteger(): bool
    {
        return parent::isNotInteger();
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function isFloat(): bool
    {
        return parent::isFloat();
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function isNotFloat(): bool
    {
        return parent::isNotFloat();
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function isEven(): bool
    {
        return parent::isEven();
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function isNotEven(): bool
    {
        return parent::isNotEven();
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function isOdd(): bool
    {
        return parent::isOdd();
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function isNotOdd(): bool
    {
        return parent::isNotOdd();
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function round(int $precision = 0, int $mode = PHP_ROUND_HALF_UP): static
    {
        return parent::round($precision, $mode);
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function ceil(int $precision = 0): static
    {
        return parent::ceil($precision);
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function floor(int $precision = 0): static
    {
        return parent::floor($precision);
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function hasSameValueAs(float|int|NumericalComparableProperty $property): bool
    {
        return parent::hasSameValueAs($property);
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function hasNotSameValueAs(float|int|NumericalComparableProperty $property): bool
    {
        return parent::hasNotSameValueAs($property);
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function is(float|ComparableProperty|int $property, bool $strict = false): bool
    {
        if (!is_int($property) && !is_float($property) && !$property instanceof self) {
            return false;
        }

        if ($strict && (is_int($property) || is_float($property))) {
            return false;
        }

        if ($strict && !$this->getUnit()->is($property->getUnit())) {
            return false;
        }

        if ($strict && !$this->getVAT()->is($property->getVAT())) {
            return false;
        }

        if ($property instanceof self) {
            $property->convertToUnit($this->getUnit());
            $property->forCountry($this->getVAT()->getCountryCode());
        }

        return $this->hasSameValueAs($property) && $this->hasSameValueWithVATAs($property);
    }

    /**
     * @inheritDoc
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function isNot(float|ComparableProperty|int $property, bool $strict = false): bool
    {
        return parent::isNot($property, $strict);
    }

    /**
     * Checks that the value with VAT is same as the value of given property.
     *
     * @param \MiBo\Properties\Contracts\ComparableProperty|float|int $property
     *
     * @return bool
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function hasSameValueWithVATAs(ComparableProperty|float|int $property): bool
    {
        if (is_int($property) || is_float($property)) {
            return $this->getValueWithVAT() === $property || (float) $this->getValueWithVAT() === (float) $property;
        }

        if (!$property instanceof self) {
            return false;
        }

        return $this->getValueWithVAT() === $property->getValueWithVAT();
    }

    /**
     * Checks that the value with VAT is not same as the value of given property.
     *
     * @param \MiBo\Properties\Contracts\ComparableProperty|float|int $property
     *
     * @return bool
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function hasNotSameValueWithVATAs(ComparableProperty|float|int $property): bool
    {
        return !$this->hasSameValueWithVATAs($property);
    }

    /**
     * Checks that the value with VAT is same as the value of given property.
     *
     * **This method converts the property if not same unit and VAT!**
     *
     * @param \MiBo\Properties\Contracts\ComparableProperty|float|int $property
     *
     * @return bool
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function isWithVATEqualTo(ComparableProperty|float|int $property): bool
    {
        if ($property instanceof self) {
            $property->convertToUnit($this->getUnit());
            $property->forCountry($this->getVAT()->getCountryCode());
        }

        return $this->hasSameValueWithVATAs($property);
    }

    /**
     * Checks that the value with VAT is same as the value of given property.
     *
     * **This method converts the property if not same unit and VAT!**
     *
     * @param \MiBo\Properties\Contracts\ComparableProperty|float|int $property
     *
     * @return bool
     *
     * @experimental This method is in experimental phase and its result may change in the future. The only
     *     reason of that is that comparing prices with different VAT rates and currencies is not a trivial
     *     task.
     * @deprecated See experimental note.
     */
    public function isWithVATNotEqualTo(ComparableProperty|float|int $property): bool
    {
        return !$this->isWithVATEqualTo($property);
    }

    // @codeCoverageIgnoreEnd

    /**
     * @inheritDoc
     */
    public function __clone(): void
    {
        parent::__clone();

        $this->prices = array_map(fn (PriceInterface $price) => clone $price, $this->prices);
    }

    /**
     * Debug info.
     *
     * @return array{
     *     price: int|float,
     *     priceWithVAT: int|float,
     *     valueOfVAT: int|float,
     *     currency: string,
     *     VAT: string,
     *     time: string|null,
     *     details: array{
     *         unit: \MiBo\Prices\Units\Price\Currency,
     *         VAT: \MiBo\VAT\VAT,
     *         prices: array<\MiBo\Prices\Contracts\PriceInterface>
     *     }
     * }
     */
    public function __debugInfo(): array
    {
        return [
            'price'        => $this->getValue(),
            'priceWithVAT' => $this->getValueWithVAT(),
            'valueOfVAT'   => $this->getValueOfVAT(),
            'currency'     => $this->getUnit()->getAlphabeticalCode(),
            'VAT'          => $this->getVAT()->getRate()->name,
            'time'         => $this->getDateTime()?->format(DateTime::ATOM),
            'details'      => [
                'unit'   => $this->getUnit(),
                'VAT'    => $this->getVAT(),
                'prices' => $this->getNestedPrices(),
            ],
        ];
    }
}
