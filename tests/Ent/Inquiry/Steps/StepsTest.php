<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Ent\Inquiry\Steps;

use Bungle\Framework\Ent\Inquiry\QueryParams;
use Bungle\Framework\Ent\Inquiry\Steps\Steps;
use MongoDB\BSON\Regex;

class StepsTest extends BaseStepTest
{
    public function testForPagingAsc(): void
    {
        $this->buildForPage();
        $this
            ->builder
            ->expects($this->exactly(2))
            ->method('sort')
            ->withConsecutive([ 'name', 1 ], ['name', -1])
        ;
        $this->params->sort = ['name', QueryParams::ORDER_ASC];

        $step = Steps::setSort;
        $step($this->ctx) ;

        $this->params->sort = ['name', QueryParams::ORDER_DESC];
        $step($this->ctx) ;
    }

    public function testForCount(): void
    {
        $this->buildForCount();
        $this ->builder ->expects($this->never())->method('sort');

        $this->params->sort = ['name', QueryParams::ORDER_DESC];
        $step = [Steps::class, 'setSort'];
        $step($this->ctx) ;
    }

    public function testNoSort(): void
    {
        $this->buildForPage();
        $this ->builder ->expects($this->never())->method('sort');
        $step = [Steps::class, 'setSort'];
        $step($this->ctx) ;
    }

    public function testSetFields(): void
    {
        $this->builder->expects($this->once())->method('select')->with(['id', 'name']);
        $setField = Steps::setFields(['id', 'name']);
        $setField($this->ctx);
    }

    public function testSetPagingForPaging(): void
    {
        $this->buildForPage();
        $this->params->pageNo = 2;

        $this->builder->expects($this->once())->method('skip')->with(50);
        $this->builder->expects($this->once())->method('limit')->with(25);

        $step = Steps::setPaging();
        $step($this->ctx);
    }

    public function testSetPagingForCount(): void
    {
        $this->buildForCount();
        $this->params->pageNo = 2;
        $this->builder->expects($this->never())->method('skip');
        $this->builder->expects($this->never())->method('limit');

        $step = Steps::setPaging();
        $step($this->ctx);
    }

    public function testAutoFieldConditions(): void
    {
        $this->params->qbe = (object) [
            'ignoreZero' => 0,
            'name' => 'foo',
            'ignoreEmpty' => '',
            'id' => 3,
            'ignoreNull' => null,
        ];
        $this
            ->builder
            ->expects($this->exactly(2))
            ->method('field')
            ->withConsecutive(['name'], ['id'])
            ->willReturn($this->builder)
        ;
        $this
            ->builder
            ->expects($this->exactly(2))
            ->method('equals')
            ->withConsecutive([new Regex('foo')], [3])
            ->willReturn($this->builder)
        ;

        $step = Steps::autoFieldCondition(array_keys(get_object_vars($this->params->qbe)));
        $step($this->ctx);
    }

    public function testFieldEqual(): void
    {
        $this->params->qbe = (object) [
            'name' => 'foo',
            'ignoreEmpty' => '',
        ];
        $this
            ->builder
            ->expects($this->once())
            ->method('field')
            ->with('name')
            ->willReturn($this->builder)
        ;

        $step = Steps::fieldEqual('name');
        $step($this->ctx);

        $step = Steps::fieldEqual('ignoreEmpty');
        $step($this->ctx);
    }
}

