<?php

declare(strict_types=1);

namespace MiBo\Prices\Tests\Core\PriceProperty;

use MiBo\Prices\Price;
use PHPUnit\Framework\TestCase;

/**
 * Class CommonPropertyTest
 *
 * @package MiBo\Prices\Tests\Core\PriceProperty
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 0.1
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 *
 * @coversDefaultClass \MiBo\Prices\Price
 */
class CommonPropertyTest extends TestCase
{
    /**
     * @small
     *
     * @covers ::getQuantityClassName
     *
     * @return void
     */
    public function testCommonPropertyMethods(): void
    {
        $this->assertSame(\MiBo\Prices\Quantities\Price::class, Price::getQuantityClassName());
    }
}
