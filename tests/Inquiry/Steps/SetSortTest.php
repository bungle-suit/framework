<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Inquiry\Steps;

use Bungle\Framework\Inquiry\QueryParams;
use Bungle\Framework\Inquiry\Steps\SetSort;

class SetSortTest extends BaseStepTest
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
        $this->params->sortTuple = ['name', QueryParams::ORDER_ASC];

        $step = new SetSort() ;
        $step($this->ctx) ;

        $this->params->sortTuple = ['name', QueryParams::ORDER_DESC];
        $step($this->ctx) ;
    }

    public function testForCount(): void
    {
        $this->buildForCount();
        $this ->builder ->expects($this->never())->method('sort');

        $this->params->sortTuple = ['name', QueryParams::ORDER_DESC];
        $step = new SetSort() ;
        $step($this->ctx) ;
    }

    public function testNoSort(): void
    {
        $this->buildForPage();
        $this ->builder ->expects($this->never())->method('sort');
        $step = new SetSort() ;
        $step($this->ctx) ;
    }
}
