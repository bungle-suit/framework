<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Inquiry;

use Bungle\Framework\Inquiry\Query;
use Bungle\Framework\Inquiry\QueryStepInterface;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class QueryTest extends MockeryTestCase
{
    /** @var EntityManagerInterface|Mockery\MockInterface  */
    private $em;
    private Query $q;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em = Mockery::mock(EntityManagerInterface::class);
        $this->q = new Query($this->em);
    }

    public function testGetSteps(): void
    {
        $step1 = Mockery::mock(QueryStepInterface::class);
        $step2 = Mockery::mock(QueryStepInterface::class);
        $step3 = Mockery::mock(QueryStepInterface::class);

        // with steps array
        $q = new Query($this->em);
        $q->buildSteps([$step1, $step3]);
        self::assertEquals([$step1, $step3], $q->getSteps());

        // with callback
        $q = new Query($this->em);
        $q->buildSteps(function () use ($step3, $step2) {
            yield $step2;
            yield $step3;
        });
        self::assertEquals([$step2, $step3], $q->getSteps());
    }

    public function testQuery(): void
    {
        $this->markTestIncomplete('wait for Builder');

        $step1 = Mockery::mock(QueryStepInterface::class);
        $step2 = Mockery::mock(QueryStepInterface::class);
        $this->q->buildSteps([$step1, $step2]);
    }

    public function testPagedQuery(): void
    {
        $this->markTestIncomplete();
    }
}
