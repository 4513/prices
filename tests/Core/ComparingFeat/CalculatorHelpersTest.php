<?php

declare(strict_types=1);

namespace MiBo\Prices\Tests\ComparingFeat;

use MiBo\Prices\Calculators\PriceCalc;
use MiBo\Prices\Price;
use MiBo\Prices\Tests\TestingComparer;
use MiBo\Prices\Tests\TestingRounder;
use MiBo\Prices\Tests\VATResolver;
use MiBo\Prices\Units\Price\Currency;
use MiBo\VAT\Manager;
use PHPUnit\Framework\TestCase;

/**
 * Class CalculatorHelpersTest
 *
 * @package MiBo\Prices\Tests\ComparingFeat
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 1.2
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 *
 * @coversDefaultClass \MiBo\Prices\Calculators\PriceCalc
 */
class CalculatorHelpersTest extends TestCase
{
    /**
     * @small
     *
     * @covers ::setCalculatorHelper
     * @covers ::setComparerHelper
     * @covers ::__callStatic
     * @covers ::getVATManager
     * @covers ::setVATManager
     *
     * @return void
     */
    public function test(): void
    {
        $price = new Price(10, Currency::get('EUR'));

        try {
            $price->isLessThan(10);
            $this->fail('Failed! The ComparingFeat is set up already (Comparer)');
        } catch (\DomainException) {}

        try {
            $price->ceil();
            $this->fail('Failed! The ComparingFeat is set up already (Rounder)');
        } catch (\DomainException) {}

        PriceCalc::setCalculatorHelper(new TestingRounder());
        PriceCalc::setComparerHelper(new TestingComparer());

        $price->isLessThan(10);
        $price->ceil();

        try {
            PriceCalc::getVATManager();
        } catch (\ValueError) {}

        $helper = new VATResolver();

        PriceCalc::setVATManager(new Manager($helper, $helper, $helper));

        $this->assertInstanceOf(Manager::class, PriceCalc::getVATManager());

        $this->expectException(\BadMethodCallException::class);
        PriceCalc::someRandomMethodThatDoesNotExist();
    }
}
