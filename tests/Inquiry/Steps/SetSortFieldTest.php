<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Inquiry\Steps;

use Bungle\Framework\Inquiry\Steps\SetSortField;

class SetSortFieldTest extends BaseStepTest
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

        $step = new SetSortField('name', true) ;
        $step($this->ctx) ;

        $step = new SetSortField('name', false) ;
        $step($this->ctx) ;
    }

    public function testForCount(): void
    {
        $this->buildForCount();
        $this ->builder ->expects($this->never())->method('sort');

        $step = new SetSortField('name', true) ;
        $step($this->ctx) ;
    }
}
