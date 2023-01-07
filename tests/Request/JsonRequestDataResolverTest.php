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

    /** @dataProvider supportsProvider */
    public function testSupports($exp, $method, $type, $attrs = []): void
    {
        $arg = new ArgumentMetadata('data', $type, false, false, null, attributes: $attrs);
        $req = new Request();
        $req->setMethod($method);
        self::assertEquals($exp, $this->resolver->supports($req, $arg));
    }

    public function supportsProvider()
    {
        return [
            'method not POST' => [false, Request::METHOD_GET, IDNameJsonRequestData::class],
            'type not implement JsonRequestDataInterface' => [false, Request::METHOD_POST, 'int'],
            'type implement JsonRequestDataInterface' => [
                true,
                Request::METHOD_POST,
                IDNameJsonRequestData::class,
            ],
            'defines JsonRequestType attribute' => [
                true,
                Request::METHOD_POST,
                'array',
                [new JsonRequestType('foo[]')],
            ],
        ];
    }

    /** @dataProvider resolveProvider */
    public function testResolve($exp, $type, $json, $attributes): void
    {
        $req = new Request([], [], [], [], [], [], $json);
        $req->setMethod(Request::METHOD_POST);
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
