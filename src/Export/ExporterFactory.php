<?php

declare(strict_types=1);

namespace Bungle\Framework\Export;

use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ExporterFactory
{
    public const SERVICE_TAG = 'bungle.importer';

    private ContainerInterface $container;

    public function __construct(#[TaggedLocator(self::SERVICE_TAG)] ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getImporter(string $importerClass): AbstractExporter
    {
        return $this->container->get($importerClass);
    }
}
