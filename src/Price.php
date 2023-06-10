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
    }

    /**
     * @inheritDoc
     */
    public function add(ContractNumericalProperty|float|int $value): static
    {
        // Adding incompatible types.
        if ($value instanceof ContractNumericalProperty && !$value instanceof Price) {
            throw new ValueError();
        }

        // Adding float or int with no VAT specified is forbidden when having combined VAT.
        if (!$value instanceof Price && $this->getVAT()->isCombined()) {
            throw new ValueError();
        }

        // Transforming float and int into Price.
        if (is_int($value) || is_float($value)) {
            $value = new self($value, $this->unit, $this->vat, $this->time);
        }

        [
            $copy,
            $vat,
        ] = PriceCalc::add($this, $value);

        $this->vat = $vat;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function subtract(ContractNumericalProperty|float|int $value): static
    {
        // Adding float or int with no VAT specified is forbidden when having combined VAT.
        if (!$value instanceof Price && $this->getVAT()->isCombined()) {
            throw new ValueError();
        }

        if (!$value instanceof Price) {
            $value = new self($value, $this->unit, $this->vat, $this->time);
        }

        PriceCalc::subtract($this, $value);

        return $this;
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
        return $this->getValue() + $this->getValueOfVAT();
    }

    /**
     * @inheritDoc
     */
    public function getValueOfVAT(): int|float
    {
        $value = 0;

        foreach ($this->prices as $price) {
            $v = $price->getVAT()->getRate();
            $v1 = $price->getValue();
            $v2 = PriceCalc::getValueOfVAT($price);
            $v3 = ProxyResolver::getPercentageOf($price->getVAT());

            $value += PriceCalc::getValueOfVAT($price);
        }

        if ($this->getVAT()->isNotCombined()) {
            $value += PriceCalc::getValueOfVAT($this);
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function forCountry(string $countryCode): static
    {
        $this->vat = ProxyResolver::retrieveByCategory($this->vat->getCategory() ?? "", $countryCode);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function __clone(): void
    {
        parent::__clone();

        $this->prices = array_map(fn (Price $price) => clone $price, $this->prices);
    }
}
