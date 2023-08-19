# Prices

[![codecov](https://codecov.io/gh/4513/prices/branch/main/graph/badge.svg?token=lbatKMmLir)](https://codecov.io/gh/4513/prices)

The library serves a Price class that can be used to represent a price in an object that can be sold or has
its value.

Goals:
* Calculating multiple prices;
* Calculating multiple prices with different currencies;
* Calculating multiple prices with different tax rates;
* Combination of any or all above.

User of this library should mainly use `\MiBo\Prices\Price` class, that comes with an implementation of
`\MiBo\Prices\Contracts\PriceInterface`, or `\MiBo\Properties\Contracts\NumericalProperty`, meaning, the class
follows native `add`, `subtract` methods. The `PriceInterface` extends the `NumericalProperty` Interface by
adding `getValueOfVAT` and `getValueWithVAT` methods that can be used to get value of either VAT or both, VAT
and the base value. The Interface then extends the `PropertyInterface` by `forCountry` method that can be used
when one wants to change the VAT of the Price for a specific country.

---
### Installation
```bash
composer require mibo/prices
```

### Usage
```php
$price = new \MiBo\Prices\Price(10, \MiBo\Prices\Units\Price\Currency::get("EUR"), \MiBo\VAT\VAT::get("SVK"));
$price->getValue(); // 10
$price->getValueOfVAT(); // 2 (20% of 10 for the day of writing this)
$price->getValueWithVAT(); // 12 (10 + 2)

$price->forCountry("CZE");
$price->getValueWithVAT(); // 11.5 (10 + 1.5 for the day of writing this)
```

**NOTE:**  
* Before using conversion of currencies, one must require a library that provides conversion rates. The conversion
  is not part of this library!
* Before using conversion of VAT rates, one must require a library that provides VAT rates. The conversion
  is not part of this library!

---
### Logic of the library
This library one extends MiBo\Properties library by a Price, using MiBo\VAT to get VAT for the Prices, and MiBo\Currencies
for currencies - units of the Prices.

#### Calculators
**PriceCalc** class is there to calculate multiple prices, either adding them together, or subtracting them. The
point is to have this process on the single place. The Calculator checks the currencies and VAT rates.

#### Price
The `\MiBo\Prices\Price` class, the (main) property, is a class-to-be-used by a user. One should not have a need
to use another class.

---
### Changes, updates, etc.
The library does not cover a conversion between currencies, nor VAT rates. If one wants to have that solved,
check composer suggestions. If empty, the libraries that are focused on that are not yet published, however, they
are under development and will be available soon.

---
### Notes
Please, be aware that comparing prices is complex and should be done with care. Currently, the library does provide
a way to compare prices, however, that is being marked as deprecated and experimental, even tho, it should work fine.
One should make sure that the behavior is as expected, because comparing a price with VAT and currency is way too hard.
