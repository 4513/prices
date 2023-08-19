<?php

declare(strict_types=1);

namespace MiBo\Prices\Tests;

/**
 * Enum ComparingStatusEnum
 *
 * @package MiBo\Prices\Tests
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 1.1
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
enum ComparingStatusEnum
{
    case VAT_EQUALS;
    case VAT_NOT_EQUALS;
    case PRICE_EQUALS;
    case PRICE_SMALLER;
    case PRICE_GREATER;
    case PRICE_WITH_VAT_EQUALS;
    case PRICE_WITH_VAT_SMALLER;
    case PRICE_WITH_VAT_GREATER;
    case PRICE_CHANGED_EQUALS;
    case PRICE_CHANGED_SMALLER;
    case PRICE_CHANGED_GREATER;
    case VALUE_WITH_VAT_EQUALS;
    case IS_SAME;
    case IS_SAME_STRICT;
}
