<?php
declare(strict_types=1);

namespace Bungle\Framework\Inquiry\Steps;

use Bungle\Framework\Inquiry\StepContext;

class SetSortField
{
    private string $field;
    private bool $isAsc;

    public function __construct(string $field, bool $isAsc)
    {
        $this->field = $field;
        $this->isAsc = $isAsc;
    }

    public function __invoke(StepContext $context)
    {
        if (!$context->isBuildForCount()) {
            $context->getBuilder()->sort($this->field, $this->isAsc ? 1 : -1);
        }
    }
}
