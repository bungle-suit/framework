<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Encoding\CSV;

use Bungle\Framework\Encoding\CSV\CSVRow;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Traversable;

use function Bungle\Framework\Encoding\CSV\excelColNameToIndex;

class CSVRowTest extends MockeryTestCase
{
    /**
     * @dataProvider createProvider
     * @param Array<int|string> $headers
     * @param null|string[] $row
     * @param string[] $rowCreated
     */
    public function testCreate(array $headers, ?array $row, array $rowCreated): void
    {
        $r = new CSVRow($headers, $row);
        self::assertEquals($headers, $r->getHeaders());
        self::assertEquals($rowCreated, $r->getRow());
    }

    /**
     * @return array<mixed[]>
     */
    public function createProvider(): array
    {
        $headers = ['a', 'b', 'c'];

        return [
            'empty' => [[], null, []],
            'row exactly count' => [$headers, ['one', 'two', 'three'], ['one', 'two', 'three']],
            'null row' => [$headers, null, ['', '', '']],
            'not enough data' => [$headers, ['one'], ['one', '', '']],
            'more data' => [
                $headers,
                ['one', 'two', 'three', 'four'],
                ['one', 'two', 'three', 'four'],
            ],
        ];
    }

    public function testIterate(): void
    {
        $row = new CSVRow(range(0, 2), ['a', 'b', 'c']);
        self::assertEquals(['a', 'b', 'c'], iterator_to_array($row));
    }

    public function testCount(): void
    {
        $row = new CSVRow(range(0, 2), ['a', 'b', 'c']);
        self::assertEquals(3, count($row));

        $row = new CSVRow(range(0, 2), ['a', 'b', 'c', 'd']);
        self::assertEquals(4, count($row));
    }

    /**
     * @dataProvider arrayAccessProvider
     * @param int|string $idx
     */
    public function testArrayAccess(CSVRow $row, $idx, string $valExp): void
    {
        if ($valExp === 'not exist') {
            self::assertFalse(isset($row[$idx]));

            return;
        }

        self::assertTrue(isset($row[$idx]));
        self::assertEquals($valExp, $row[$idx]);
        $row[$idx] = 'new value';
        self::assertEquals('new value', $row[$idx]);
    }

    /**
     * @return Traversable<mixed>
     */
    public function arrayAccessProvider(): Traversable
    {
        $empty = new CSVRow([]);
        $names = new CSVRow(['one', 'two', 'three'], ['1', '2', '3']);
        $byIntegers = new CSVRow([0, 1, 2], ['one', 'two', 'three']);

        yield 'by name out range' => [$empty, 'blah', 'not exist'];
        yield 'by excel col out range' => [$empty, 'A', 'not exist'];
        yield 'by number index col out range' => [$empty, 0, 'not exist'];

        $names = clone $names;
        yield 'by name' => [$names, 'two', '2'];

        $names = clone $names;
        yield 'by name 2' => [$names, 'three', '3'];

        $byIntegers = clone $byIntegers;
        yield 'by int' => [$byIntegers, 2, 'three'];

        $names = clone $names;
        yield 'by excel' => [$names, 'C', '3'];
    }

    /**
     * @dataProvider excelColNameToIndexProvider
     * @param int|false $exp
     */
    public function testExcelColNameToIndex($exp, string $col): void
    {
        self::assertEquals($exp, excelColNameToIndex($col));
    }

    /**
     * @return mixed[]
     */
    public function excelColNameToIndexProvider(): array
    {
        return [
            'not a'=> [false, 'a'],
            'not AAA' => [false, 'AAA'],
            'A'=> [0, 'A'],
            'Z' => [25, 'Z'],
            'AA' => [26, 'AA'],
            'AZ' => [51, 'AZ'],
            'ZZ' => [701, 'ZZ'],
        ];
    }
}
