<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Inquiry;

use ArrayIterator;
use Aura\SqlQuery\Common\SelectInterface;
use Bungle\Framework\Inquiry\Builder;
use Bungle\Framework\Inquiry\ColumnMeta;
use Bungle\Framework\Inquiry\QBEMeta;
use Bungle\Framework\Inquiry\Query;
use Bungle\Framework\Inquiry\QueryParams;
use Bungle\Framework\Inquiry\QueryStepInterface;
use Countable;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Hamcrest\Matchers;
use Mockery;
use Symfony\Component\PropertyInfo\Type;
use Traversable;

class QueryTest extends Mockery\Adapter\Phpunit\MockeryTestCase
{
    private Mockery\LegacyMockInterface|EntityManagerInterface|Mockery\MockInterface $em;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em = Mockery::mock(EntityManagerInterface::class);
    }

    public function testQuery(): void
    {
        $qb = Mockery::mock(QueryBuilder::class);
        $this->em->expects('createQueryBuilder')
                 ->andReturn($qb);
        $dqlQuery = Mockery::mock(AbstractQuery::class);
        $qb->expects('getQuery')
           ->andReturn($dqlQuery);
        $dqlQuery->expects('toIterable')
                 ->with()
                 ->andReturn(
                     new ArrayIterator(
                         [
                             ['line1'],
                             ['line2'],
                             ['line3'],
                         ]
                     ),
                 );

        $params = new QueryParams(0, []);
        $step1 = Mockery::mock(QueryStepInterface::class);
        $step2 = Mockery::mock(QueryStepInterface::class);
        $step1->expects('__invoke')
              ->with(
                  Mockery::on(
                      fn(Builder $builder) => $builder->getQueryParams() === $params &&
                          $builder->getQueryBuilder() === $qb
                  )
              );
        $step2->expects('__invoke')
              ->with(Mockery::type(Builder::class));
        $col1 = new ColumnMeta('[id]', 'id', new Type(Type::BUILTIN_TYPE_INT));
        $q1 = new QBEMeta('fooMeta', 'lbl', new Type(Type::BUILTIN_TYPE_INT, true));
        $q = new Query(
            $this->em,
            [
                $step1,
                $step2,
                function (Builder $builder) use ($q1, $col1): void {
                    $builder->addColumn($col1, 'foo');
                    $builder->addQBE($q1);
                },
            ]
        );

        $iter = $q->query($params);
        self::assertEquals(['foo' => $col1], $q->getColumns());
        self::assertEquals([['line1'], ['line2'], ['line3']], iterator_to_array($iter, false));
    }

    public function testPagedQuery(): void
    {
        $paginator = Mockery::mock('overload:\Doctrine\ORM\Tools\Pagination\Paginator', Traversable::class, Countable::class);
        $paginator->expects('count')->andReturn(11);
        $paginator->expects('setUseOutputWalkers')->with(false);
        $paginator->expects('getIterator')->with()->andReturn(
            new ArrayIterator([
                ['line1'],
                ['line2'],
            ])
        );

        $qb = Mockery::mock(QueryBuilder::class);
        $this->em->expects('createQueryBuilder')
                 ->andReturn($qb);
        $dqlQuery = Mockery::mock(AbstractQuery::class);
        $qb->expects('getQuery')
           ->andReturn($dqlQuery);

        $params = new QueryParams(0, []);
        $col1 = new ColumnMeta('[id]', 'id', new Type(Type::BUILTIN_TYPE_INT));
        $pagingStep = Mockery::namedMock('pagingStep', QueryStepInterface::class);
        $q = new class(
            $this->em,
            [
                function (Builder $builder) use ($col1): void {
                    $builder->addColumn($col1, 'foo');
                },
            ],
            $pagingStep,
        ) extends Query {
            private QueryStepInterface $pagingStep;

            public function __construct(
                EntityManagerInterface $em,
                array $steps,
                QueryStepInterface $pagingStep
            ) {
                parent::__construct($em, $steps);
                $this->pagingStep = $pagingStep;
            }

            protected function createExtraPagingSteps(): array
            {
                return [$this->pagingStep];
            }
        };
        $pagingStep->expects('__invoke')
                   ->with(Mockery::on(fn(Builder $builder) => count($builder->getColumns()) === 1));

        $pagedData = $q->pagedQuery($params);
        self::assertEquals(11, $pagedData->getCount());
        self::assertEquals([['line1'], ['line2']], $pagedData->getData());
        self::assertEquals(['foo' => $col1], $q->getColumns());
    }

    public function testNativePagedQuery(): void
    {
        $countHit = 0;
        $step = function (Builder $builder) use (&$countHit): void {
            /** @var SelectInterface $qb */
            $qb = $builder->getQueryBuilder();
            if ($builder->isBuildForCount()) {
                $countHit++;
                $qb->cols(['count(0)'])->from('tbl')->where('id > ?', 12);
            } else {
                $qb->cols(['id', 'name'])->from('tbl')->where('id > ?', 12);
            }
        };
        $conn = Mockery::mock(Connection::class);
        $this->em->expects('getConnection')->andReturn($conn)->twice();
        $conn->expects('fetchOne')->with(
            Mockery::on(
                fn($s) => preg_replace('/\s+/', ' ', $s) === 'SELECT count(0) FROM `tbl` WHERE id > :_1_'
            ), ['_1_' => 12]
        )->andReturn(100);
        $resultIter = new ArrayIterator(
            $rows = [
                ['id' => 13, 'name' => 'foo'],
                ['id' => 14, 'name' => 'bar'],
            ]
        );
        $conn->expects('executeQuery')
             ->with(
                 Mockery::on(
                     fn($s) => preg_replace('/\s+/', ' ', $s) ===
                         'SELECT id, name FROM `tbl` WHERE id > :_1_ LIMIT 25 OFFSET 250'
                 ),
                 ['_1_' => 12]
             )
             ->andReturn($resultIter);

        $q = new class($this->em, [$step]) extends Query {
            public function __construct(EntityManagerInterface $em, array $steps)
            {
                parent::__construct($em, $steps);
                $this->setNativeMode(true);
            }
        };
        $params = new QueryParams(10, []);
        $act = $q->pagedQuery($params);
        self::assertEquals(100, $act->getCount());
        self::assertEquals($rows, $act->getData());
        self::assertEquals(1, $countHit);
    }

    public function testCount(): void
    {
        $paginator = Mockery::mock(
            'overload:\Doctrine\ORM\Tools\Pagination\Paginator',
            Traversable::class,
            Countable::class
        );
        $paginator->expects('count')->andReturn(11);
        $paginator->expects('setUseOutputWalkers')->with(false);

        $qb = Mockery::mock(QueryBuilder::class);
        $this->em->expects('createQueryBuilder')
                 ->andReturn($qb);
        $dqlQuery = Mockery::mock(AbstractQuery::class);
        $qb->expects('getQuery')
           ->andReturn($dqlQuery);

        $params = new QueryParams(0, []);
        $col1 = new ColumnMeta('[id]', 'id', new Type(Type::BUILTIN_TYPE_INT));
        $pagingStep = Mockery::namedMock('pagingStep', QueryStepInterface::class);
        $q = new class(
            $this->em,
            [
                function (Builder $builder) use ($col1): void {
                    $builder->addColumn($col1, 'foo');
                },
            ],
            $pagingStep,
        ) extends Query {
            private QueryStepInterface $pagingStep;

            public function __construct(
                EntityManagerInterface $em,
                array $steps,
                QueryStepInterface $pagingStep
            ) {
                parent::__construct($em, $steps);
                $this->pagingStep = $pagingStep;
            }

            protected function createExtraPagingSteps(): array
            {
                return [$this->pagingStep];
            }
        };
        $pagingStep->expects('__invoke')
                   ->with(Mockery::on(fn(Builder $builder) => count($builder->getColumns()) === 1));

        self::assertEquals(11, $q->count($params));
        self::assertEquals(['foo' => $col1], $q->getColumns());
    }

    public function testBuildQBEMeta(): void
    {
        $qb = Mockery::mock(QueryBuilder::class);
        $this->em->expects('createQueryBuilder')
                 ->andReturn($qb);

        $params = new QueryParams(0, []);
        $col1 = new ColumnMeta('[id]', 'id', new Type(Type::BUILTIN_TYPE_INT));
        $q1 = new QBEMeta('fooMeta', 'lbl', new Type(Type::BUILTIN_TYPE_INT, true));
        $q = new Query(
            $this->em,
            [
                function (Builder $builder) use ($q1, $col1): void {
                    self::assertTrue($builder->isBuildForQBE());
                    $builder->addColumn($col1, 'foo');
                    $builder->addQBE($q1);
                },
            ]
        );

        $QBEs = $q->buildQBEMetas($params);
        self::assertEquals(['fooMeta' => $q1], $q->getQBEMetas());
        self::assertSame($QBEs, $q->getQBEMetas());
    }

    public function testNativeQuery(): void
    {
        $conn = Mockery::mock(Connection::class);
        $this->em->expects('getConnection')->andReturn($conn);
        $resultIter = new ArrayIterator();
        $step1 = new class() implements QueryStepInterface {
            public function __invoke(Builder $builder): void
            {
                /** @var SelectInterface $qb */
                $qb = $builder->getQueryBuilder();
                $qb->from('order as o')
                   ->cols(['o.id', 'o.name'])
                   ->where('o.id = ?', 12);
            }
        };
        $conn
            ->expects('executeQuery')
            ->with(Matchers::containsString('FROM'), ['_1_' => 12])
            ->andReturn($resultIter);
        $q = new Query($this->em, [$step1]);
        $q->setNativeMode(true);
        $params = new QueryParams(0, []);
        self::assertSame([], iterator_to_array($q->query($params), false));
    }
}
