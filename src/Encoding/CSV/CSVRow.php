<?php

declare(strict_types=1);

namespace Bungle\Framework\Encoding\CSV;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use LogicException;
use Traversable;

/**
 * Access a csv row by header name or excel like column index, such as 'A' for first cell.
 * @implements ArrayAccess<int|string, string>
 * @implements IteratorAggregate<string>
 */
class CSVRow implements ArrayAccess, Countable, IteratorAggregate
{
    /** @var Array<int|string> */
    private array $headers;

    /** @var string[] */
    private array $row;

    /**
     * @param Array<int|string> $headers , header names, if header not available,
     *        pass range(0, n-1) as header.
     * @param string[] $row
     *
     * If $row not specified, will create count($headers) empty strings.
     * If count($row) < count($headers), append empty strings until count($row) = count($headers).
     */
    public function __construct(array $headers, array $row = null)
    {
        $this->headers = $headers;
        /** @var string[] $row */
        $row = $row ? array_values($row) : [];
        $this->row = $row;
        if (($n = count($this->headers) - count($this->row)) > 0) {
            $this->row = [
                ...$this->row,
                ...array_fill(0, $n, ''),
            ];
        }
    }

    /**
     * Iterate row values.
     * @return Traversable<string>
     */
    public function getIterator(): Traversable
    {
        yield from $this->row;
    }

    /**
     * @param int|string $offset
     */
    public function offsetExists($offset): bool
    {
        $idx = self::translateOffset($offset);

        return $idx !== -1;
    }

    /**
     * @param int|string $offset
     */
    public function offsetGet($offset): string
    {
        $idx = self::translateOffset($offset);

        return $this->row[$idx];
    }

    /**
     * @param int|string $offset
     * @param string $value
     */
    public function offsetSet($offset, $value): void
    {
        $idx = self::translateOffset($offset);
        $this->row[$idx] = $value;
    }

    /**
     * @param int|string $offset
     */
    public function offsetUnset($offset): void
    {
        throw new LogicException("offsetUnset not supported");
    }

    /**
     * @param int|string $offset
     */
    private function translateOffset($offset): int
    {
        if (is_int($offset)) {
            $idx = $offset;
        } else {
            $byExcel = excelColNameToIndex($offset);
            if ($byExcel === false) {
                /** @var int|false $r */
                $r = array_search($offset, $this->headers);
                if ($r === false) {
                    return -1;
                } else {
                    return $r;
                }
            }
            $idx = $byExcel;
        }

        if ($idx < 0 || $idx >= count($this->row)) {
            return -1;
        }
        return $idx;
    }

    public function count(): int
    {
        return count($this->row);
    }

    /**
     * @return array<int|string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return string[]
     */
    public function getRow(): array
    {
        return $this->row;
    }
}

/**
 * @return false|int
 */
function excelColNameToIndex(string $col)
{
    $isExcel = preg_match('/^[A-Z]{1,2}$/', $col);
    assert($isExcel !== false);
    if ($isExcel === 0) {
        return false;
    }

    $r = 0;
    for ($l = strlen($col), $i = 0; $i < $l; $i++) {
        $r = 26 * $r + (ord($col[$i]) - ord('A') + 1);
    }

    return $r - 1;
}
