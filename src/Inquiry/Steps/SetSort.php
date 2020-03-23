<?php
declare(strict_types=1);

namespace Bungle\Framework\Inquiry\Steps;

use Bungle\Framework\Inquiry\StepContext;

class SetSort
{
    public function __invoke(StepContext $context)
    {
        $sort = $context->getParams()->sortTuple;
        if ($sort && !$context->isBuildForCount()) {
            $context->getBuilder()->sort($sort[0], $sort[1] ? 1 : -1);
        }
    }
}
