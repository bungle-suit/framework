<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests;

use Bungle\Framework\Converter;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    /** @dataProvider newBoolToStringProvider */
    public function testNewBoolToString($exp, $val, $trueText = null, $falseText = null): void
    {
        $f = Converter::newBoolToString($trueText ?? '是', $falseText ?? '否');
        self::assertEquals($exp, $f($val));
    }

    public function newBoolToStringProvider()
    {
        return [
            'null' => ['', null],
            'falsy' => ['否', 0],
            'truthy' => ['是', 'no'],
            'true' => ['yes', true, 'yes', 'no'],
            'false' => ['', false, 'yes', ''],
        ];
    }

    public function testFormat(): void
    {
        $f = [Converter::class, 'format'];

        // null
        self::assertEquals('', $f(null));

        // bool
        self::assertEquals('是', $f(true));
        self::assertEquals('否', $f(false));

        // float
        self::assertEquals('100.00', $f(100.0));
        self::assertEquals('10,000.00', $f(10000.0));
        self::assertEquals('1.95', $f(1.954));
        self::assertEquals('1.95', $f(1.945));

        // DateTime
        $d = new DateTime('2011-01-02T05:03:01.012345');
        self::assertEquals('2011-01-02 05:03', $f($d));
        $d = new DateTimeImmutable('2011-01-01T13:03:01.012345');
        self::assertEquals('2011-01-01 13:03', $f($d));
        $d = new DateTimeImmutable('2011-01-01T00:00:00.0');
        self::assertEquals('2011-01-01', $f($d));

        // other types
        self::assertEquals(' abc ', $f(' abc '));
        self::assertEquals('100', $f(100));
    }

    public function testFormatYearMonth(): void
    {
        self::assertEquals('', Converter::formatYearMonth(null));
        self::assertEquals('2020-09', Converter::formatYearMonth(new DateTime('2020-09-30')));
        self::assertEquals('2021-09', Converter::formatYearMonth(new DateTime('2021-09-30')));
    }

    public function testMinLength(): void
    {
        self::assertEquals("", Converter::justifyAlign("", 2));
        // 对于一个字，在前面插入一个全角空格，达到右对齐效果
        self::assertEquals("　a", Converter::justifyAlign("a", 2));
        self::assertEquals("abc", Converter::justifyAlign("abc", 1));
        self::assertEquals("abc", Converter::justifyAlign("abc", 0));
        self::assertEquals("姓　名", Converter::justifyAlign("姓名", 3));
        self::assertEquals("姓　　　　名", Converter::justifyAlign("姓名", 6));
        self::assertEquals("销 售 单", Converter::justifyAlign("销售单", 4));
        self::assertEquals("销售 入 库", Converter::justifyAlign("销售入库", 5));
        self::assertEquals("现款 销售 单", Converter::justifyAlign("现款销售单", 6));
        self::assertEquals("现 款 销 售 单", Converter::justifyAlign("现款销售单", 7));
        self::assertEquals("现 款　销 售　单", Converter::justifyAlign("现款销售单", 8));
    }

    public function testParseNullDateTime(): void
    {
        self::assertNull(Converter::parseNullDateTime(null));
        self::assertNull(Converter::parseNullDateTime(''));
        self::assertEquals(new DateTime('2020-09-18'), Converter::parseNullDateTime('2020-09-18'));
    }

    /**
     * @dataProvider formatYMDProvider
     */
    public function testFormatYMD(string $exp, ?DateTime $d): void
    {
        self::assertEquals($exp, Converter::formatYMD($d));
    }

    /**
     * @return array<mixed[]>
     */
    public function formatYMDProvider(): array
    {
        return [
            ['', null],
            ['2020-09-03', new DateTime('2020-09-03')],
            ['2021-09-03', new DateTime('2021-09-03')],
        ];
    }

    /**
     * @dataProvider formatBytesProvider
     */
    public function testFormatBytes(string $exp, int $size): void
    {
        self::assertEquals($exp, Converter::formatBytes($size));
    }

    /**
     * @return array<mixed[]>
     */
    public function formatBytesProvider(): array
    {
        return [
            ['0', 0],
            ['1023', 1023],
            ['1K', 1024],
            ['1K', 1025],
            ['1.48K', 1512],
            ['1024K', 1048575],
            ['1M', 1048576],
            ['1G', 1048576 * 1024],
            ['1T', 1048576 * 1024 * 1024],
            ['1P', 1048576 * 1024 * 1024 * 1024],
            ['1E', 1048576 * 1024 * 1024 * 1024 * 1024],
        ];
    }

    /**
     * @dataProvider ellipsisProvider
     */
    public function testEllipsis(string $exp, string $s, int $n = 5): void
    {
        self::assertEquals($exp, Converter::ellipsis($n, $s));
    }

    /**
     * @return array<mixed[]>
     */
    public function ellipsisProvider(): array
    {
        return [
            'empty' => ['', ''],
            'less than' => ['abcd', 'abcd'],
            'exact' => ['abcde', 'abcde'],
            'more' => ['abcd…', 'abcdef'],
            'utf' => ['汉字abc', '汉字abc'],
            'utf2' => ['汉字又如何', '汉字又如何'],
            'utf ellipsis' => ['汉字又如…', '汉字又如何啊'],
        ];
    }
}
