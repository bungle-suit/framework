<?php
declare(strict_types=1);

namespace Bungle\Framework\Export\ParamParser;

use Bungle\Framework\Model\HasAttributes;
use Bungle\Framework\Model\HasAttributesInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Context for parsing qbe values.
 */
class ExportContext implements HasAttributesInterface
{
    use HasAttributes;

    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}
