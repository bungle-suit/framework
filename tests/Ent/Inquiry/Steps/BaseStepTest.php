<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Ent\Inquiry\Steps;

use Bungle\Framework\Ent\Inquiry\QueryParams;
use Bungle\Framework\Ent\Inquiry\StepContext;
use Bungle\Framework\Tests\Ent\Inquiry\Order;
use Doctrine\ODM\MongoDB\Query\Builder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

abstract class BaseStepTest extends TestCase
{
    /**
     * @var Builder|MockObject;
     */
    protected $builder;
    protected QueryParams $params;
    protected StepContext $ctx;

    public function setUp(): void
    {
        $this->buildForPage();
    }

    protected function buildForCount(): void {
        $this->create(true);
    }

    protected function buildForPage(): void
    {
        $this->create(false);
    }

    protected function create(bool $buildForCount): void
    {
        $this->builder = $this->createMock(Builder::class);
        $this->params = new QueryParams(Order::class, 0, new stdClass());
        $this->ctx = new StepContext($buildForCount, $this->params, $this->builder);
    }

}
