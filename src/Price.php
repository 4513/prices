<?php

declare(strict_types=1);

namespace MiBo\Prices;

use DateTime;
use MiBo\Prices\Calculators\PriceCalc;
use MiBo\Prices\Contracts\PriceInterface;
use MiBo\Prices\Traits\PriceHelper;
use MiBo\Properties\Contracts\NumericalProperty as ContractNumericalProperty;
use MiBo\Properties\Contracts\Unit;
use MiBo\Properties\NumericalProperty;
use MiBo\Properties\Value;
use MiBo\VAT\Enums\VATRate;
use MiBo\VAT\Resolvers\ProxyResolver;
use MiBo\VAT\VAT;
use ValueError;

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
     * @inheritDoc
     */
    public function __clone(): void
    {
        parent::__clone();

        $this->prices = array_map(fn (PriceInterface $price) => clone $price, $this->prices);
    }
}
