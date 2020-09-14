<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Inquiry;

use Bungle\Framework\Inquiry\Builder;
use Bungle\Framework\Inquiry\ColumnMeta;
use Bungle\Framework\Inquiry\QueryParams;
use Doctrine\ORM\QueryBuilder;
use LogicException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\PropertyInfo\Type;

class BuilderTest extends MockeryTestCase
{
    /** @var QueryBuilder|Mockery\MockInterface */
    private $qb;
    private QueryParams $params;
    private Builder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->qb = Mockery::mock(QueryBuilder::class);
        $this->params = new QueryParams(0, [], [], ['foo' => 'bar']);
        $this->builder = new Builder($this->qb, $this->params);
    }

    public function testConstructor(): void
    {
        // use option to init attributes
        self::assertEquals('bar', $this->builder->get('foo'));
        self::assertSame($this->qb, $this->builder->getQueryBuilder());
        self::assertSame($this->params, $this->builder->getQueryParams());
    }

    public function testAddColumn(): void
    {
        // add named columns
        self::assertEquals(
            'col1',
            $this->builder->addColumn($col1 = new ColumnMeta('a', 'foo', new Type(Type::BUILTIN_TYPE_INT)), 'col1')
        );
        self::assertEquals(
            'col2',
            $this->builder->addColumn($col2 = new ColumnMeta('b', 'bar', new Type(Type::BUILTIN_TYPE_STRING)), 'col2'),
        );
        self::assertEquals(
            [
                'col1' => $col1,
                'col2' => $col2,
            ],
            $this->builder->getColumns()
        );

        // add auto named column
        self::assertEquals(
            '__col_1',
            $this->builder->addColumn($col3 = new ColumnMeta('c', 'foobar', new Type(Type::BUILTIN_TYPE_STRING)))
        );
        self::assertEquals(
            '__col_2',
            $this->builder->addColumn($col4 = new ColumnMeta('d', 'foobar', new Type(Type::BUILTIN_TYPE_STRING)))
        );
        self::assertEquals(
            [
                'col1' => $col1,
                'col2' => $col2,
                '__col_1' => $col3,
                '__col_2' => $col4,
            ],
            $this->builder->getColumns()
        );

        // add dup name column
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Column "col2" already added');
        $this->builder->addColumn($col2, 'col2');
    }
}
