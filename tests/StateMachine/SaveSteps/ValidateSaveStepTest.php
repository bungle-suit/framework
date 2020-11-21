<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine\SaveSteps;

use Bungle\Framework\StateMachine\SaveStepContext;
use Bungle\Framework\StateMachine\SaveSteps\ValidateSaveStep;
use Bungle\Framework\Tests\StateMachine\Entity\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidateSaveStepTest extends TestCase
{
    private Order $ord;
    /** @var ValidatorInterface|MockObject $validator */
    private $validator;
    /** @var ConstraintViolationListInterface|Stub $errors */
    private $errors;
    private SaveStepContext $ctx;

    private function create(bool $failed, bool $abort = false): void
    {
        $this->ord = new Order();
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->errors = $this->createStub(ConstraintViolationList::class);
        $attrs = [];
        if ($abort) {
            $attrs['validated'] = true;
        }
        $this->ctx = new SaveStepContext($attrs);

        if ($failed) {
            $this->errors->method('count')->willReturn(1);
            $this->errors->method('__toString')->willReturn('Validation error');
        } else {
            $this->errors->method('count')->willReturn(0);
        }

        if ($abort) {
            $this->validator->expects($this->never())->method('validate');
        } else {
            $this->validator->expects($this->once())->method('validate')
                ->with($this->ord)->willReturn($this->errors);
        }
    }

    private function callStep(): ?string
    {
        $step = new ValidateSaveStep($this->validator);
        return $step($this->ord, $this->ctx);
    }

    public function testValidateFailed()
    {
        $this->create(true);
        self::assertEquals('Validation error', $this->callStep());
    }

    public function testValidateSucceed(): void
    {
        $this->create(false);
        self::assertNull($this->callStep());
    }

    public function testSkipped(): void
    {
        $this->create(false, true);
        self::assertNull($this->callStep());
    }

    public function testValidationListToString(): void
    {
        $list = new ConstraintViolationList([]);
        self::assertEquals('', ValidateSaveStep::validationListToString($list));
    }
}
