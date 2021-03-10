<?php

declare(strict_types=1);

namespace Bungle\Framework\Encoding\CSV;

use Bungle\Framework\Export\FS;
use Bungle\Framework\FP;
use CallbackFilterIterator;
use Generator;
use Iterator;
use Webmozart\Assert\Assert;

use function Symfony\Component\String\u;

class CSVDecoder
{
    /**
     * Read csv content from file/stream $f, options:
     *
     * charset: convert from charset to utf-8, if specified.
     * noHeader: treat first row as data, CSVRow indexed as [0..n).
     * doNotCloseFile: do not close $f.
     *
     * If contains only head row, returns empty Traversable, to access
     * the header, use result value ->getReturn() method.
     *
     * Trim begin/end from values.
     *
     * @param resource $f
     * @param array{charset?: string, noHeader?: bool, doNotCloseFile?: bool} $options
     * @return Generator<CSVRow>
     */
    public static function decode($f, array $options = []): Generator
    {
        if ($charset = $options['charset'] ?? false) {
            stream_filter_append($f, 'convert.iconv.'.$charset.'.utf-8', STREAM_FILTER_READ);
        }

        try {
            while (true) {
                $header = fgetcsv($f);
                if ($header === false) {
                    return [];
                }

                Assert::notNull($header, 'read csv file failed');
                if ($header[0] === null) {
                    continue;
                }
                array_walk($header, fn(string &$s) => $s = u($s)->trim()->toString());

                if ($options['noHeader'] ?? false) {
                    $row = $header;
                    $header = range(0, count($row) - 1);
                    yield new CSVRow($header, $row);
                }
                break;
            }

            while (($line = fgetcsv($f)) !== false) {
                Assert::notNull($line, 'read csv file failed 2');
                if ($line[0] === null) {
                    continue;
                }
                array_walk($line, fn(string &$s) => $s = u($s)->trim()->toString());
                yield new CSVRow($header, $line);
            }

            return $header;
        } finally {
            if (!($options['doNotCloseFile'] ?? false)) {
                fclose($f);
            }
        }
    }

    /**
     * @param array{charset?: string, noHeader?: bool, doNotCloseFile?: bool} $options
     * @return Generator<CSVRow>
     */
    public static function decodeString(string $s, array $options = []): Generator
    {
        $f = FS::stringStream($s);

        return self::decode($f, $options);
    }

    /**
     * Filter out empty rows. Empty row is a row all cell value are empty string.
     * @param Iterator<CSVRow> $iter
     * @return Iterator<CSVRow>
     */
    public static function ignoreEmptyRows(Iterator $iter): Iterator
    {
        return new CallbackFilterIterator(
            $iter,
            fn(CSVRow $row) => FP::any(fn(string $s) => !!$s, $row)
        );
    }
}
