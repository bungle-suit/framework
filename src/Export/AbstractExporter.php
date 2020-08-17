<?php
declare(strict_types=1);

namespace Bungle\Framework\Export;

use Bungle\Framework\Export\ParamParser\ExportContext;
use Bungle\Framework\Export\ParamParser\ParamParser;
use Bungle\Framework\Export\ParamParser\ParamValueParserInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Traversable;

abstract class AbstractExporter
{
    /** @required */
    public FSInterface $fs;
    /** @required */
    public LoggerInterface $logger;

    /**
     * Generate the exported file's name.
     * @param mixed[] $params
     */
    abstract public function buildFilename(array $params): string;

    /**
     * @param mixed[] $qbes
     * @return string file path of where generated content stored.
     *
     * If runtime error occurred during export, will save error messages
     * in a text file, logic name is 'error.txt'.
     */
    public function export(ExportContext $context): ExportResult
    {
        try {
            $params = $this->parseParams($context);
            $fn = $this->fs->createTempFile('bungle-export');
            $this->doBuild($fn, $params);
            return new ExportResult($fn, $this->buildFilename($params));
        } catch (RuntimeException $e) {
            if (isset($this->logger)) {
                $this->logger->error($e->getMessage(), ['exception' => $e]);
            }
            if (isset($fn)) {
                $this->fs->removeFile($fn);
            }
            return $this->genErrorFile($e);
        }
    }

    /**
     * @phpstan-return Traversable<QBEValueParserInterface|callable(QBEParseContext): mixed[]|string>
     * @return Traversable<ParamValueParserInterface|callable>
     */
    abstract protected function buildParamParser(): Traversable;

    abstract protected function doBuild(string $fn, array $params): void;

    /**
     * @return mixed[]
     */
    private function parseParams(ExportContext $context): array
    {
        $parsers = iterator_to_array($this->buildParamParser(), false);
        $p = new ParamParser($parsers);
        return $p->parse($context);
    }

    private function genErrorFile(RuntimeException $e): ExportResult
    {
        return new ExportResult(
            $this->fs->createTempFile('bungle-export-error', $e->getMessage()),
            'error.txt'
        );
    }
}
