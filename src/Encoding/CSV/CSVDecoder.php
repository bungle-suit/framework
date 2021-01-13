<?php

declare(strict_types=1);

namespace Bungle\Framework\Encoding\CSV;

use Generator;
use Webmozart\Assert\Assert;

class CSVDecoder
{
    /**
     * Read csv content from file/stream $f, options:
     *
     * charset: convert from charset to utf-8, if specified.
     * noHeader: treat first row as data, CSVRow indexed as [0..n).
     *
     * If contains only head row, returns empty Traversable, to access
     * the header, use result value ->getReturn() method.
     *
     * @param resource $f
     * @param array{charset?: string, noHeader?: bool} $options
     * @return Generator<CSVRow>
     */
    public static function decode($f, array $options = []): Generator
    {
        if ($charset = $options['charset'] ?? false) {
            stream_filter_append($f, 'convert.iconv.'.$charset.'.utf-8', STREAM_FILTER_READ);
        }

        while (true) {
            $header = fgetcsv($f);
            if ($header === false) {
                return [];
            }

            Assert::notNull($header, 'read csv file failed');
            if ($header[0] === null) {
                continue;
            }

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
            yield new CSVRow($header, $line);
        }

        return $header;
    }
}
