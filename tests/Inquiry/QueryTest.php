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
use Symfony\Component\PropertyInfo\Type;
use Traversable;

beforeEach(function () {
    $this->em = Mockery::mock(EntityManagerInterface::class);
});

it('query', function () {
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
    expect($q->getColumns())->toEqual(['foo' => $col1]);
    expect(iterator_to_array($iter, false))->toEqual([['line1'], ['line2'], ['line3']]);
});

it('paged query', function () {
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
    expect($pagedData->getCount())->toEqual(11);
    expect($pagedData->getData())->toEqual([['line1'], ['line2']]);
    expect($q->getColumns())->toEqual(['foo' => $col1]);
});

it('build QBE Meta', function () {
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
    expect($q->getQBEMetas())->toEqual(['fooMeta' => $q1]);
    expect($q->getQBEMetas())->toBe($QBEs);
});

it('not allow paged query in native mode', function () {
    $q = new Query(
        $this->em,
        []
    );
    $q->setNativeMode(true);
    $q->pagedQuery(new QueryParams(0, []));
})->expectExceptionMessage('Not support paged query when in native mode');
