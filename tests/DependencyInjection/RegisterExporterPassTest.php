<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\DependencyInjection;

use Bungle\Framework\Export\AbstractExporter;
use Bungle\Framework\Export\ExporterFactory;
use Bungle\FrameworkBundle\DependencyInjection\RegisterExporterPass;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterExporterPassTest extends MockeryTestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $container->register(ExporterFactory::class, ExporterFactory::class);
        $exporter1 = Mockery::mock(AbstractExporter::class);
        $exporter2 = Mockery::mock(AbstractExporter::class);
        $container->register($exporter1::class, $exporter1::class)->addTag(
            ExporterFactory::SERVICE_TAG
        );
        $container->register($exporter2::class, $exporter2::class)->addTag(
            ExporterFactory::SERVICE_TAG
        );

        (new RegisterExporterPass())->process($container);
        /** @var ExporterFactory $factory */
        $factory = $container->get(ExporterFactory::class);
        self::assertInstanceOf($exporter1::class, $factory->getExporter($exporter1::class));
        self::assertInstanceOf($exporter2::class, $factory->getExporter($exporter2::class));
    }
}
