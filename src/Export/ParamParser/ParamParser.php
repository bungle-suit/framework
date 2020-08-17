<?php
declare(strict_types=1);

namespace Bungle\Framework\Export\ParamParser;

use RuntimeException;

/**
 * Parse params from request.
 */
class ParamParser
{
    /**
     * @phpstan-var Array<ParamValueParserInterface|callable(ExportContext): ?string>
     */
    private array $parsers;

    /**
     * @phpstan-param Array<ParamValueParserInterface|callable(ExportContext): ?string> $parsers
     */
    public function __construct(array $parsers)
    {
        $this->parsers = $parsers;
    }

    /**
     * @return mixed[] parsed qbe.
     * @throws RuntimeException if any parser returns error string.
     */
    public function parse(ExportContext $context): array
    {
        foreach ($this->parsers as $parser) {
            $rv = $parser($context);
            if (is_string($rv)) {
                throw new RuntimeException($rv);
            }
        }
        return $context->all();
    }
}
