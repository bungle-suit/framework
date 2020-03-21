<?php

declare(strict_types=1);

namespace Bungle\Framework\Inquiry;

/**
 * It is a mark interface that all qbe values need to implement.
 *
 * Values stored in QBE objects, can be two kinds of value:
 *
 * 1. Plain value, values not implement QBEValueInterface,
 *    they will convert to Conditions\Equals condition
 * 1. QBEValue implement QBEValueInterface, QBEValueBuilder
 *    will take settings from QBEValue, and build the query.
 *
 * QBEValueBuilder create by QBEValueBuilderFactory.
 */
interface QBEValueInterface
{
}
