<?php

declare(strict_types=1);

namespace Bungle\Framework\Inquiry\Steps;

use Bungle\Framework\Inquiry\StepContext;

/**
 * Set Query offset/limit to fetch data in specific page.
 */
class SetPaging
{
    /**
     * @var int
     */
    private int $recsPerPage;

    public function __construct(int $recsPerPage = 25)
    {
        $this->recsPerPage = $recsPerPage;
    }

    public function __invoke(StepContext $context)
    {
        if ($context->isBuildForCount()) {
            return;
        }

        $context->getBuilder()->skip($this->recsPerPage * $context->getParams()->pageNo);
        $context->getBuilder()->limit($this->recsPerPage);
    }
}
