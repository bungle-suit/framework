<?php

declare(strict_types=1);

namespace Bungle\Framework\Encoding\CSV;

use function Symfony\Component\String\u;

class CSVEncoder
{
    public const DOS_EOL = "\r\n";

    /** @var resource */
    private $f;
    private string $eol;
    /** @var false|resource */
    private $filter = false;

    /**
     * @param resource $f
     * @param array{charset?: string, eol?: string} $options
     *
     * Options:
     * charset: convert to charset from utf-8 if specified,
     * eol: stop using PHP_EOL if specified, such as DOS_EOL
     */
    public function __construct($f, array $options = [])
    {
        $this->f = $f;
        $this->eol = $options['eol'] ?? PHP_EOL;
        if ($charset = $options['charset'] ?? false) {
            $this->filter = stream_filter_append(
                $f,
                'convert.iconv.utf-8.'.$charset,
                STREAM_FILTER_WRITE
            );
        }
    }

    /**
     * @param string[] $headers
     */
    public function writeHeader(array $headers): void
    {
        fwrite($this->f, implode(',', $headers));
        fwrite($this->f, $this->eol);
    }

    public function writeRow(CSVRow $row): void
    {
        $row = $row->getRow();
        array_walk(
            $row,
            function (string &$s) {
                $s = u($s)->replace(',', '\,')->toString();
            }
        );
        fwrite($this->f, implode(',', $row));
        fwrite($this->f, $this->eol);
    }
}
