<?php

declare(strict_types=1);

namespace MiBo\Prices\Traits;

use MiBo\Prices\Contracts\PriceInterface;
use MiBo\Properties\Value;

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
    /** @var array<string, array<string, \MiBo\Prices\Price>> */
    protected array $prices = [
        '+' => [],
        '-' => [],
    ];

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

        if (!isset($this->prices['+'][$category])) {
            $this->prices['+'][$category] = $price;

            return;
        }

        $this->prices['+'][$category]->add($price);
    }

    /**
     * @inheritDoc
     */
    public function getNestedPrice(string $category): ?PriceInterface
    {
        return $this->prices['+'][$category] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getNestedPrices(): array
    {
        return $this->prices;
    }
}
