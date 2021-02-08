<?php

declare(strict_types=1);

namespace Bungle\Framework\Ent\Code;

use LogicException;
use Webmozart\Assert\Assert;

/**
 * @template T
 * @implements CoderInterface<T>
 */
abstract class AbstractCoder implements CoderInterface
{
    /**
     * @var array<CoderStepInterface<T>|callable(T, CodeContext): ?string> $steps
     */
    private array $steps;

    /**
     * @param array<CoderStepInterface<T>|callable(T, CodeContext): ?string> $steps
     */
    public function __construct(array $steps)
    {
        $this->steps = $steps;
    }

    function __invoke($entity, CodeContext $context = null): string
    {
        $context = $context ?? new CodeContext();

        for ($step = current($this->steps); $step !== false; $step = next($this->steps)) {
            try {
                CodeSteps::runStep($step, $entity, $context);
            } catch (CoderOverflowException $e) {
                $carries = $context->getCarriageSteps();
                $allOverflowed = true;
                for ($carry = end($carries); $carry !== false; $carry = prev($carries)) {
                    $idx=key($carries);
                    Assert::notNull($idx);
                    try {
                        $context->setSection($idx, $carry->carry($entity, $context));
                        $allOverflowed = false;
                        break;
                    } catch (CoderOverflowException $e) {
                    }
                }
                if ($allOverflowed) {
                    throw new LogicException('All CarriagableCode out of code space');
                }
                for ($carry = next($carries); $carry !== false; $carry = next($carries)) {
                    $idx=key($carries);
                    Assert::notNull($idx);
                    $context->setSection($idx, $carry->carry($entity, $context));
                }
                CodeSteps::runStep($step, $entity, $context);
            }
        }

        return strval($context);
    }
}
