<?php

declare(strict_types=1);

namespace MiBo\Prices\Tests;

use MiBo\Prices\Contracts\PriceCalculatorHelper;
use MiBo\Prices\Contracts\PriceInterface;

/**
 * Class TestingRounder
 *
 * @package MiBo\Prices\Tests
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 1.2
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
class TestingRounder implements PriceCalculatorHelper
{
    public function round(PriceInterface $price, int $precision = 0, int $mode = PHP_ROUND_HALF_UP): array
    {
        return [$price->getVAT()->getClassification()->getCode() => round($price->getValue(), $precision, $mode)];
    }

    public function ceil(PriceInterface $price, int $precision = 0): array
    {
        return [$price->getVAT()->getClassification()->getCode() => ceil($price->getValue())];
    }

    public function floor(PriceInterface $price, int $precision = 0): array
    {
        return [$price->getVAT()->getClassification()->getCode() => floor($price->getValue())];
    }
}
