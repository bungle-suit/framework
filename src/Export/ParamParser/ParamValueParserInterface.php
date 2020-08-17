<?php
declare(strict_types=1);

namespace Bungle\Framework\Export\ParamParser;

/**
 * Parses QBE Value, normally parse a qbe value.
 */
interface ParamValueParserInterface
{
    /**
     * @return ?string Return string on error, parsed value saved to $context
     */
    public function __invoke(ExportContext $context);
}
