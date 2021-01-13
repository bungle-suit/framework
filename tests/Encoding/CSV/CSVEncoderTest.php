<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Encoding\CSV;

use Bungle\Framework\Encoding\CSV\CSVEncoder;
use Bungle\Framework\Encoding\CSV\CSVRow;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CSVEncoderTest extends MockeryTestCase
{
    public function test(): void
    {
        $f = self::newRWStream();
        $encoder = new CSVEncoder($f);
        $headers = ['a', 'b', 'c'];
        $encoder->writeHeader($headers);
        $encoder->writeRow(new CSVRow($headers, ['1', '2', '3']));
        $encoder->writeRow(new CSVRow($headers, ['4', '汗,6', '6']));

        self::assertFile(
            <<<'csv'
            a,b,c
            1,2,3
            4,汗\,6,6

            csv,
            $f
        );
    }

    public function testEOLOption(): void
    {
        $f = self::newRWStream();
        $encoder = new CSVEncoder($f, ['eol' => CSVEncoder::DOS_EOL]);
        $headers = ['a', 'b', 'c'];
        $encoder->writeHeader($headers);
        $encoder->writeRow(new CSVRow($headers, ['1', '2', '3']));

        self::assertFile(
            str_replace(
                PHP_EOL,
                "\r\n",
                <<<'csv'
            a,b,c
            1,2,3

            csv
            ),
            $f
        );
    }

    public function testCharset(): void
    {
        $f = self::newRWStream();
        $encoder = new CSVEncoder($f, ['charset' => 'gb18030']);
        $headers = ['a', '啊', 'c'];
        $encoder->writeHeader($headers);
        $encoder->writeRow(new CSVRow($headers, ['1', '2', '饿']));

        self::assertFile(
            mb_convert_encoding(
                <<<'csv'
            a,啊,c
            1,2,饿

            csv,
                'gb18030',
                'utf-8'
            ),
            $f
        );
    }

    /**
     * @param false|string $exp
     * @param resource $f
     */
    private static function assertFile($exp, $f): void
    {
        self::assertNotFalse($exp);

        rewind($f);
        $act = stream_get_contents($f);
        self::assertNotFalse($act);
        self::assertEquals($exp, $act);
    }

    /**
     * @return resource
     */
    private static function newRWStream()
    {
        $r = fopen('php://memory', 'r+');
        self::assertNotFalse($r);

        return $r;
    }
}
