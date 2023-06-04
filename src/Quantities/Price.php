<?php

declare(strict_types = 1);

namespace MiBo\Prices\Quantities;

use MiBo\Prices\Units\Price\Currency;
use MiBo\Properties\Contracts\Quantity;
use MiBo\Properties\Contracts\Unit;
use MiBo\Properties\Traits\QuantityHelper;

/**
 * Class Price
 *
 * @package MiBo\Prices\Quantities
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since x.x
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
class Price implements Quantity
{
    use QuantityHelper;

    public static function getDimensionSymbol(): string
    {
        return "PRICE";
    }

    public static function getDefaultProperty(): string
    {
        return \MiBo\Prices\Price::class;
    }

    protected static function getInitialUnit(): Unit
    {
        return Currency::get();
    }
}
