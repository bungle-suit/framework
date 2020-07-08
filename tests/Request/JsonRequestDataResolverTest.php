<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Request;

use Bungle\Framework\Request\JsonRequestDataResolver;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class JsonRequestDataResolverTest extends MockeryTestCase
{
    private JsonRequestDataResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $this->resolver = new JsonRequestDataResolver($serializer);
    }

    public function testSupports(): void
    {
        $arg = new ArgumentMetadata('data', IDNameJsonRequestData::class, false, false, null);
        $req = new Request();
        $req->setMethod(Request::METHOD_POST);
        self::assertTrue($this->resolver->supports($req, $arg));

        $req->setMethod(Request::METHOD_GET);
        self::assertFalse($this->resolver->supports($req, $arg));
        $req->setMethod(Request::METHOD_POST);

        $arg = new ArgumentMetadata('data', self::class, false, false, null);
        self::assertFalse($this->resolver->supports($req, $arg));

        $arg = new ArgumentMetadata('data', null, false, false, null);
        self::assertFalse($this->resolver->supports($req, $arg));

        $arg = new ArgumentMetadata('data', 'int', false, false, null);
        self::assertFalse($this->resolver->supports($req, $arg));
    }

    public function testResolve(): void
    {
        $req = new Request([], [], [], [], [], [], '{"name":"foo","ids":["a", "b"]}');
        $arg = new ArgumentMetadata('data', IDNameJsonRequestData::class, false, false, null);
        $arr = iterator_to_array($this->resolver->resolve($req, $arg));
        self::assertCount(1, $arr);
        /** @var IDNameJsonRequestData $data */
        $data = $arr[0];
        self::assertInstanceOf(IDNameJsonRequestData::class, $data);
        self::assertEquals('foo', $data->getName());
        self::assertEquals(['a', 'b'], $data->getIds());
    }
}
