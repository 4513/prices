<?php

declare(strict_types=1);

namespace MiBo\Prices\Tests\Core\Taxonomies;

use MiBo\Prices\Taxonomies\AnyTaxonomy;
use MiBo\Prices\Taxonomies\CombinedTaxonomy;
use MiBo\VAT\Enums\VATRate;
use PHPUnit\Framework\TestCase;

/**
 * Class TaxonomiesTest
 *
 * @package MiBo\Prices\Tests\Core\Taxonomies
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 2.0
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
class TaxonomiesTest extends TestCase
{
    /**
     * @small
     *
     * @covers \MiBo\Prices\Taxonomies\AnyTaxonomy::get
     * @covers \MiBo\Prices\Taxonomies\AnyTaxonomy::getCode
     * @covers \MiBo\Prices\Taxonomies\AnyTaxonomy::is
     * @covers \MiBo\Prices\Taxonomies\AnyTaxonomy::wraps
     * @covers \MiBo\Prices\Taxonomies\AnyTaxonomy::belongsTo
     *
     * @return void
     */
    public function testAny(): void
    {
        $any = AnyTaxonomy::get();

        $this->assertTrue($any->is(AnyTaxonomy::get()));
        $this->assertTrue($any->is($any->getCode()));
        $this->assertSame(VATRate::ANY->name, $any->getCode());
        $this->assertFalse($any->wraps($any));
        $this->assertFalse($any->belongsTo($any));
        $this->assertTrue($any::isValid($any->getCode()));
        $this->assertFalse($any::isValid('invalid'));
    }

    /**
     * @small
     *
     * @covers \MiBo\Prices\Taxonomies\CombinedTaxonomy::get
     * @covers \MiBo\Prices\Taxonomies\CombinedTaxonomy::getCode
     * @covers \MiBo\Prices\Taxonomies\CombinedTaxonomy::is
     * @covers \MiBo\Prices\Taxonomies\CombinedTaxonomy::wraps
     * @covers \MiBo\Prices\Taxonomies\CombinedTaxonomy::belongsTo
     *
     * @return void
     */
    public function testCombined(): void
    {
        $combined = CombinedTaxonomy::get();

        $this->assertTrue($combined->is(CombinedTaxonomy::get()));
        $this->assertTrue($combined->is($combined->getCode()));
        $this->assertSame(VATRate::COMBINED->name, $combined->getCode());
        $this->assertFalse($combined->wraps($combined));
        $this->assertFalse($combined->belongsTo($combined));
        $this->assertTrue($combined::isValid($combined->getCode()));
        $this->assertFalse($combined::isValid('invalid'));
    }
}
