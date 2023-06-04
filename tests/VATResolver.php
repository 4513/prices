<?php

declare(strict_types = 1);

namespace MiBo\Prices\Tests;

use DateTime;
use MiBo\VAT\Contracts\Convertor;
use MiBo\VAT\Contracts\Resolver;
use MiBo\VAT\Enums\VATRate;
use MiBo\VAT\VAT;

/**
 * Class VATResolver
 *
 * @package MiBo\Prices\Tests
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since x.x
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
class VATResolver implements Resolver, Convertor
{
    public static function convertForCountry(VAT $vat, string $countryCode): VAT
    {
        return self::retrieveByCategory($vat->getCategory(), $countryCode);
    }

    public static function retrieveByCategory(string $category, string $countryCode): VAT
    {
        switch ($category) {
            case "123":
            return VAT::get($countryCode);

            case "456":
            return VAT::get($countryCode, VATRate::NONE);

            default:
            return VAT::get($countryCode, VATRate::SECOND_REDUCED);
        }
    }

    public static function getPercentageOf(VAT $vat, ?DateTime $time = null): float|int
    {
        switch ($vat->getRate()->name) {
            case VATRate::STANDARD->name:
            return 0.21;

            case VATRate::NONE->name:
            return 0;

            default:
            return 0.15;
        }
    }
}
