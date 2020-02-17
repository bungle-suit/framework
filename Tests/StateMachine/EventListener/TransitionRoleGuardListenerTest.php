<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine\EventListener;

use PHPUnit\Framework\TestCase;
use Bungle\Framework\StateMachine\EventListener\TransitionRoleGuardListener;
use Bungle\Framework\Tests\StateMachine\Entity\Order;

final class TransitionRoleGuardListenerTest extends TestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $listener = new TransitionRoleGuardListener(
            new FakeAuthorizationChecker('ROLE_ord_save')
        );
        $this->dispatcher->addListener('workflow.guard', $listener);
    }

    public function testCan(): void
    {
        self::assertTrue($this->sm->can($this->ord, 'save'));

        $this->ord->setState('saved');
        self::assertFalse($this->sm->can($this->ord, 'check'));
    }
}
