<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Inquiry\Steps;

use Bungle\Framework\Inquiry\QueryParams;
use Bungle\Framework\Inquiry\StepContext;
use Bungle\Framework\Tests\Inquiry\Order;
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
        $this->builder = $this->createMock(Builder::class);
        $this->params = new QueryParams(Order::class, 0, new stdClass());
        $this->ctx = new StepContext(true, $this->params, $this->builder);
    }
}
