<?php

declare(strict_types=1);

namespace MiBo\Prices\Traits;

use MiBo\Prices\Contracts\PriceInterface;
use MiBo\Properties\Contracts\NumericalProperty;
use MiBo\Properties\Value;
use ValueError;

/**
 * Trait PriceHelper
 *
 * @package MiBo\Prices\Traits
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 0.1
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
trait PriceHelper
{
    /** @var array<string, \MiBo\Prices\Contracts\PriceInterface> */
    protected array $prices = [];

    /**
     * @inheritDoc
     */
    abstract public function getNumericalValue(): Value;

    /**
     * @inheritDoc
     *
     * @internal For Calculating within the library only.
     */
    public function setNestedPrice(string $category, PriceInterface $price): void
    {
        $this->getNumericalValue()->add($price->getNumericalValue());

        if (!isset($this->prices[$category])) {
            $this->prices[$category] = $price;

            return;
        }

        $this->prices[$category]->getNumericalValue()->add($price->getNumericalValue());
    }

    /**
     * @inheritDoc
     */
    public function multiply(int|float|NumericalProperty $value): static
    {
        parent::multiply($value);

        $this->initialValue->multiply($value instanceof NumericalProperty ? $value->getNumericalValue() : $value);

        foreach ($this->prices as $price) {
            $price->multiply($value);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function divide(NumericalProperty|float|int $value): static
    {
        parent::divide($value);

        $this->initialValue->divide($value instanceof NumericalProperty ? $value->getNumericalValue() : $value);

        foreach ($this->prices as $price) {
            $price->divide($value);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getNestedPrice(string $category): ?PriceInterface
    {
        return $this->prices[$category] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getNestedPrices(): array
    {
        return $this->prices;
    }
}
