<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Inquiry;

use ArrayIterator;
use Bungle\Framework\Inquiry\Builder;
use Bungle\Framework\Inquiry\ColumnMeta;
use Bungle\Framework\Inquiry\QBEMeta;
use Bungle\Framework\Inquiry\Query;
use Bungle\Framework\Inquiry\QueryParams;
use Bungle\Framework\Inquiry\QueryStepInterface;
use Countable;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\PropertyInfo\Type;
use Traversable;

class QueryTest extends MockeryTestCase
{
    /** @var EntityManagerInterface|Mockery\MockInterface */
    private $em;

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
        $dqlQuery->expects('iterate')
                 ->with(null, AbstractQuery::HYDRATE_ARRAY)
                 ->andReturn(
                     new ArrayIterator(
                         [
                             new ArrayIterator([['line1'], ['line2']]),
                             new ArrayIterator([['line3']]),
                             new ArrayIterator([]),
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
        self::assertEquals(
            [['line1'], ['line2'], ['line3']],
            iterator_to_array($iter, false)
        );
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
                   ->with(
                       Mockery::on(
                           fn(Builder $builder) => !$builder->isBuildForCount() &&
                               count($builder->getColumns()) === 1
                       )
                   );

        $pagedData = $q->pagedQuery($params);
        self::assertEquals(11, $pagedData->getCount());
        self::assertEquals([['line1'], ['line2']], $pagedData->getData());
        self::assertEquals(['foo' => $col1], $q->getColumns());
    }

    public function testBuildQBEMetas(): void
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
}
