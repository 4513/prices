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
        return self::retrieveByCategory($vat->getCategory() ?? "", $countryCode);
    }

    public static function retrieveByCategory(string $category, string $countryCode): VAT
    {
        if (empty(self::getVATs()[$countryCode][$category])) {
            return VAT::get($countryCode, VATRate::STANDARD, $category);
        }

        return VAT::get($countryCode, self::getVATs()[$countryCode][$category], $category);

//        return match ($category) {
//            "123"   => VAT::get($countryCode),
//            "456"   => VAT::get($countryCode, VATRate::NONE),
//            default => VAT::get($countryCode, VATRate::SECOND_REDUCED),
//        };
        /**
         * 10 11.5
         * 15 17.25
         * 20 22
         * 25 27.5
         * 30 30
         *
         * 25 27.5
         */
    }

    public static function getPercentageOf(VAT $vat, ?DateTime $time = null): float|int
    {
        return self::getPercentages()[$vat->getCountryCode()][$vat->getRate()->name];
    }

    /**
     * @return array<string, array<string, \MiBo\VAT\Enums\VATRate>>
     */
    private static function getVATs(): array
    {
        return [
            "CZE" => [
                "9705 00 00" => VATRate::REDUCED,
                "9704 00 00" => VATRate::REDUCED,
                "2201"       => VATRate::SECOND_REDUCED,
                "06"         => VATRate::NONE,
                "07"         => VATRate::NONE,
                "08"         => VATRate::NONE,
                "09"         => VATRate::NONE,
                "10"         => VATRate::NONE,
            ],
            "SVK" => [
                "07" => VATRate::REDUCED,
                "08" => VATRate::REDUCED, // 36.75
            ],
        ];
    }

    /**
     * @return array<string, array<value-of<\MiBo\VAT\Enums\VATRate>, float>>
     */
    private static function getPercentages(): array
    {
        return [
            "CZE" => [
                VATRate::STANDARD->name       => 0.21,
                VATRate::SECOND_REDUCED->name => 0.10,
                VATRate::REDUCED->name        => 0.15,
                VATRate::NONE->name           => 0,
            ],
            "SVK" => [
                VATRate::STANDARD->name => 0.20,
                VATRate::REDUCED->name  => 0.10,
                VATRate::NONE->name     => 0,
            ],
        ];
    }
}
