<?php
declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader;

/**
 * Callback based SectionBoundaryInterface
 */
class SectionBoundary implements SectionBoundaryInterface
{
    private $isSectionStart;
    private $isSectionEnd;

    public function __construct(callable $isSectionStart, callable $isSectionEnd)
    {
        $this->isSectionStart = $isSectionStart;
        $this->isSectionEnd = $isSectionEnd;
    }

    public function isSectionStart(ExcelReader $reader): bool
    {
        return ($this->isSectionStart)($reader);
    }

    public function isSectionEnd(ExcelReader $reader): bool
    {
        return ($this->isSectionEnd)($reader);
    }

    public function onReadComplete(ExcelReader $reader): void
    {
    }

    /**
     * Shortcut of `new MatchFirstBoundary($boundary)`
     */
    public static function matchFirst(SectionBoundaryInterface $boundary): SectionBoundaryInterface
    {
        return new MatchFirstSectionBoundary($boundary);
    }

    /**
     * Returns callable that returns true if current sheet name is one of $sheetNames
     *
     * @return callable(ExcelReader): bool
     */
    public static function sheetNameIs(string ...$sheetNames): callable
    {
        return function (ExcelReader $reader) use ($sheetNames): bool {
            return in_array($reader->getSheet()->getTitle(), $sheetNames);
        };
    }
}
