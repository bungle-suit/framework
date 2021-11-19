<?php

declare(strict_types=1);

namespace Bungle\Framework\Export;

class ExporterFactory
{
    public const SERVICE_TAG = 'bungle.exporter';

    /** @var AbstractExporter[] */
    private array $exporters = [];

    public function getExporter(string $exporterClass): AbstractExporter
    {
        return $this->exporters[$exporterClass];
    }

    public function addExporter(AbstractExporter $exporter): void
    {
        $this->exporters[$exporter::class] = $exporter;
    }
}
