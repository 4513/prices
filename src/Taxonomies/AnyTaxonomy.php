<?php

declare(strict_types=1);

namespace MiBo\Prices\Taxonomies;

use MiBo\Taxonomy\Contracts\ProductTaxonomy;
use MiBo\VAT\Enums\VATRate;

/**
 * Class AnyTaxonomy
 *
 * @package MiBo\Prices\Taxonomies
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 2.0
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class AnyTaxonomy implements ProductTaxonomy
{
    private static ?self $instance = null;

    /**
     * Returns the instance.
     *
     * @return static
     */
    public static function get(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return VATRate::ANY->name;
    }

    /**
     * @inheritDoc
     */
    public function is(string|ProductTaxonomy $code): bool
    {
        return $code instanceof ProductTaxonomy
            ? $code->getCode() === $this->getCode()
            : $code === $this->getCode();
    }

    /**
     * @inheritDoc
     */
    public function belongsTo(string|ProductTaxonomy $code): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function wraps(string|ProductTaxonomy $code): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public static function isValid(string $code): bool
    {
        return $code === VATRate::ANY->name;
    }
}
