<?php
declare(strict_types=1);

namespace Bungle\Framework\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Inject JsonRequestDataInterface argument from post data into controller argument.
 */
class JsonRequestDataResolver implements ValueResolverInterface
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

        if ($argument->getAttributes(JsonRequestType::class)) {
            return true;
        }

        if ($argument->getType() === null) {
            return false;
        }

        if (!str_contains($argument->getType(), '\\')) {
            return false;
        }

        $interfaces = class_implements($argument->getType());
        return in_array(JsonRequestDataInterface::class, $interfaces);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (!$this->supports($request, $argument)) {
            return [];
        }

        $t = $argument->getType();
        /** @var JsonRequestType[] $attrs */
        $attrs = $argument->getAttributes(JsonRequestType::class);
        if ($attrs) {
            $t = $attrs[0]->type;
        }
        yield $this->serializer->deserialize($request->getContent(), $t, 'json');
    }
}
