<?php

declare(strict_types=1);

namespace MiBo\Prices;

use DateTime;
use DateTimeInterface;
use MiBo\Prices\Calculators\PriceCalc;
use MiBo\Prices\Contracts\PriceInterface;
use MiBo\Prices\Taxonomies\AnyTaxonomy;
use MiBo\Prices\Traits\PriceComparing;
use MiBo\Prices\Traits\PriceHelper;
use MiBo\Properties\Contracts\NumericalProperty as ContractNumericalProperty;
use MiBo\Properties\Contracts\Unit;
use MiBo\Properties\NumericalProperty;
use MiBo\Properties\Value;
use MiBo\VAT\Enums\VATRate;
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
    use PriceComparing;
    use PriceHelper;

    protected VAT $vat;

    /** @var \MiBo\Prices\Units\Price\Currency */
    protected Unit $unit;

    protected DateTime $time;

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
        $time     ??= new DateTime();
        $this->time = $time;
        $this->vat  = $vat === null ? VAT::get('', VATRate::ANY, AnyTaxonomy::get(), $time) : $vat;

        $value = $value instanceof Value ?
            $value :
            new Value($value, $unit->getMinorUnitRate() ?? 0, 0);

        parent::__construct($value, $unit);

        $this->initialValue = (clone $this->getNumericalValue())->multiply(0);
        $this->initialVAT   = clone $this->getVAT();

        $this->prices[$this->getVAT()->getClassification()->getCode()] = clone $this;
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
        $this->setNestedPrice($value->getVAT()->getClassification()->getCode(), $value);

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
        $this->vat = PriceCalc::getVATManager()
            ->retrieveVAT($this->vat->getClassification(), $countryCode, $this->getDateTime());

        foreach ($this->prices as $price) {
            $price->forCountry($countryCode);
        }

        return $this;
    }

    /**
     * Datetime of the price.
     *
     * @return \DateTimeInterface
     */
    public function getDateTime(): DateTimeInterface
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
     */
    public function round(int $precision = 0, int $mode = PHP_ROUND_HALF_UP): static
    {
        $values = PriceCalc::round($this, $precision, $mode);

        $this->multiply(0);

        foreach ($values as $category => $value) {
            $this->prices[$category]->add($value);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function ceil(int $precision = 0): static
    {
        $values = PriceCalc::ceil($this, $precision);

        $this->multiply(0);

        foreach ($values as $category => $value) {
            $this->prices[$category]->add($value);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function floor(int $precision = 0): static
    {
        $values = PriceCalc::floor($this, $precision);

        $this->multiply(0);

        foreach ($values as $category => $value) {
            $this->prices[$category]->add($value);
        }

        return $this;
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
     *         unit: array<mixed>,
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
            'time'         => $this->getDateTime()->format(DateTime::ATOM),
            'details'      => [
                'unit'   => $this->getUnit()->__debugInfo(),
                'VAT'    => $this->getVAT(),
                'prices' => $this->getNestedPrices(),
            ],
        ];
    }
}
