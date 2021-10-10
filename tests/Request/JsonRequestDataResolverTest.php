<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Request;

use Bungle\Framework\Request\JsonRequestDataResolver;
use Bungle\Framework\Request\JsonRequestType;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class JsonRequestDataResolverTest extends MockeryTestCase
{
    private JsonRequestDataResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $serializer = new Serializer(
            [new ObjectNormalizer(), new ArrayDenormalizer()],
            [new JsonEncoder()]
        );
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

    /** @dataProvider resolveProvider */
    public function testResolve($exp, $type, $json, $attributes): void
    {
        $req = new Request([], [], [], [], [], [], $json);
        $arg = new ArgumentMetadata('data', $type, false, false, null, attributes: $attributes);
        $arr = iterator_to_array($this->resolver->resolve($req, $arg));
        self::assertEquals([$exp], $arr);
    }

    public function resolveProvider()
    {
        $data = new IDNameJsonRequestData();
        $data->setIds(['a', 'b']);
        $data->setName('foo');
        $json = '{"name":"foo","ids":["a", "b"]}';

        return [
            'instance' => [$data, IDNameJsonRequestData::class, $json, []],
            'array' => [
                [$data],
                'array',
                "[$json]",
                [new JsonRequestType(IDNameJsonRequestData::class.'[]')],
            ],
        ];
    }
}
