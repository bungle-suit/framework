<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Encoding\CSV;

use Assert\Assertion;
use Bungle\Framework\Encoding\CSV\CSVDecoder;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CSVDecoderTest extends MockeryTestCase
{
    public function testDecodeHasHeader(): void
    {
        $f = self::stringStream(
            <<<'CSV'
            a,b,c,d
            1,2,3,4
            5,6,7,8
            CSV
        );

        $gen = CSVDecoder::decode($f);
        $rowIdx = 0;
        foreach ($gen as $row) {
            if ($rowIdx === 0) {
                self::assertEquals('1', $row['a']);
                self::assertEquals('4', $row['D']);
            } elseif ($rowIdx === 1) {
                self::assertEquals('6', $row['b']);
                self::assertEquals('7', $row['C']);
            }
            $rowIdx++;
        }
        self::assertEquals(2, $rowIdx);
        self::assertEquals(['a', 'b', 'c', 'd'], $gen->getReturn());
    }

    public function testDOSEOL(): void
    {
        // test not enable auto_detect_line_endings can still compatible with dos end-line.
        self::assertEquals('0', ini_get('auto_detect_line_endings'));

        $f = self::stringStream(
            str_replace(
                "\n",
                "\r\n",
                <<<'CSV'
            1,2,3
            a,b,c
            CSV
            )
        );

        $gen = CSVDecoder::decode($f, ['noHeader' => true]);
        $rowIdx = 0;
        foreach ($gen as $row) {
            if ($rowIdx === 0) {
                self::assertEquals('1', $row[0]);
                self::assertEquals('3', $row['C']);
            } elseif ($rowIdx === 1) {
                self::assertEquals('b', $row[1]);
                self::assertEquals('a', $row['A']);
            }
            $rowIdx++;
        }
        self::assertEquals(2, $rowIdx);
        self::assertEquals([0, 1, 2], $gen->getReturn());
    }

    public function testDecodeNoHeader(): void
    {
        $f = self::stringStream(
            <<<'CSV'
            1,2,3
            a,b,c
            CSV
        );

        $gen = CSVDecoder::decode($f, ['noHeader' => true]);
        $rowIdx = 0;
        foreach ($gen as $row) {
            if ($rowIdx === 0) {
                self::assertEquals('1', $row[0]);
                self::assertEquals('3', $row['C']);
            } elseif ($rowIdx === 1) {
                self::assertEquals('b', $row[1]);
                self::assertEquals('a', $row['A']);
            }
            $rowIdx++;
        }
        self::assertEquals(2, $rowIdx);
        self::assertEquals([0, 1, 2], $gen->getReturn());
    }

    public function testEmpty(): void
    {
        $f = self::stringStream('');
        $gen = CSVDecoder::decode($f);
        self::assertEquals(0, iterator_count($gen));
        self::assertEquals([], $gen->getReturn());
    }

    public function testHasHeaderNoRows(): void
    {
        $f = self::stringStream(
            <<<'CSV'
            a,b,c,d
            CSV
        );

        $gen = CSVDecoder::decode($f);
        self::assertEquals(0, iterator_count($gen));
        self::assertEquals(['a', 'b', 'c', 'd'], $gen->getReturn());
    }

    public function testIgnoreEmptyRows(): void
    {
        $f = self::stringStream(
            <<<'CSV'

            a,b,c,d

            1,2,3,4

            5,6,7,8


            CSV
        );

        $gen = CSVDecoder::decode($f);
        $rows = iterator_to_array($gen);
        self::assertSame('1', $rows[0]['A']);
        self::assertSame('8', $rows[1]['d']);
        self::assertCount(2, $rows);

        self::assertEquals(['a', 'b', 'c', 'd'], $gen->getReturn());
    }

    public function testWithCharset(): void
    {
        $s = mb_convert_encoding(
            <<<'CSV'
            汉字,啊
            文明,blah
            CSV
            ,
            'GB18030',
            'utf-8'
        );
        Assertion::string($s);
        $f = self::stringStream($s);
        $gen = CSVDecoder::decode($f, ['charset' => 'GB18030']);
        foreach ($gen as $row) {
            self::assertEquals('文明', $row['A']);
            self::assertEquals('blah', $row['啊']);
        }
        self::assertEquals(['汉字', '啊'], $gen->getReturn());
    }

    /**
     * @return resource
     */
    private static function stringStream(string $s)
    {
        $f = fopen('php://memory', 'r+');
        self::assertNotFalse($f);
        fwrite($f, $s);
        rewind($f);

        return $f;
    }
}
