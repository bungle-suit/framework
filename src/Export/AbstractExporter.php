<?php

declare(strict_types=1);

namespace Bungle\Framework\Export;

use Bungle\Framework\Export\ParamParser\ExportContext;
use Bungle\Framework\Export\ParamParser\ParamParser;
use Bungle\Framework\Export\ParamParser\ParamValueParserInterface;
use Bungle\Framework\FP;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Traversable;

abstract class AbstractExporter
{
    /** @required */
    public FSInterface $fs;
    /** @required */
    public LoggerInterface $logger;

    private ExportProgress $progress;

    /** @var callable(ExportProgress): void */
    private $onProgress;

    /**
     * Generate the exported file's name.
     * @param mixed[] $params
     */
    abstract public function buildFilename(array $params): string;

    /**
     * If runtime error occurred during export, will save error messages
     * in a text file, logic name is 'error.txt'.
     * @param bool $throws throws RuntimeException if true, by
     * default RuntimeException message dumped into error file.
     */
    public function export(ExportContext $context, bool $throws = false): ExportResult
    {
        try {
            $params = $this->parseParams($context);

            return $this->exportWithParams($params);
        } catch (RuntimeException $e) {
            if (isset($this->logger)) {
                $this->logger->error($e->getMessage(), ['exception' => $e]);
            }
            if ($throws) {
                throw $e;
            }

            return $this->genErrorFile($e);
        }
    }

    public function exportWithParams(array $params): ExportResult
    {
        $this->initProgress();
        $this->onProgress = $this->onProgress ?? FP::null();

        $fn = $this->fs->tempFile();
        $this->doBuild($fn, $params);

        return new ExportResult($fn, $this->buildFilename($params));
    }

    /**
     * @return  Traversable<ParamValueParserInterface|callable(ExportContext): ?string>
     * @return Traversable<ParamValueParserInterface|callable>
     */
    abstract protected function buildParamParser(): Traversable;

    /**
     * @param array $params
     */
    abstract protected function doBuild(string $fn, array $params): void;

    /**
     * @return array
     */
    public function parseParams(ExportContext $context): array
    {
        $parsers = iterator_to_array($this->buildParamParser(), false);
        $p = new ParamParser($parsers);

        return $p->parse($context);
    }

    private function genErrorFile(RuntimeException $e): ExportResult
    {
        return new ExportResult(
            $this->fs->tempFile($e->getMessage()),
            'error.txt'
        );
    }

    protected function initProgress(): void
    {
        if (!isset($this->progress)) {
            $this->progress = new ExportProgress();
            $this->progress->total = -1;
        }
    }

    public function setOnProgress(callable $onProgress)
    {
        $this->onProgress = $onProgress;
    }

    protected function setProgressTotal(int $total): void
    {
        $this->progress->total = $total;
        ($this->onProgress)($this->progress);
    }

    protected function incProgress(int $delta = 1): void
    {
        $this->progress->current++;
        ($this->onProgress)($this->progress);
    }

    protected function sendMessage(string $message): void
    {
        $this->progress->message = $message;
        ($this->onProgress)($this->progress);
        $this->progress->message = '';
    }

    protected function sendStatus(string $status): void
    {
        $this->progress->status = $status;
        ($this->onProgress)($this->progress);
        $this->progress->status = '';
    }
}
