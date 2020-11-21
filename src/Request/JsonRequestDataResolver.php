<?php
declare(strict_types=1);

namespace Bungle\Framework\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\SerializerInterface;
use Traversable;

/**
 * Inject JsonRequestDataInterface argument from post data into controller argument.
 */
class JsonRequestDataResolver implements ArgumentValueResolverInterface
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        if ($request->getMethod() !== Request::METHOD_POST) {
            return false;
        }

        if ($argument->getType() === null) {
            return false;
        }

        if (strpos($argument->getType(), '\\') === false) {
            return false;
        }

        $interfaces = class_implements($argument->getType());
        return in_array(JsonRequestDataInterface::class, $interfaces);
    }

    /**
     * @phpstan-return Traversable<mixed>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): Traversable
    {
        $t = $argument->getType();
        assert($t !== null);
        yield $this->serializer->deserialize($request->getContent(), $t, 'json');
    }
}
