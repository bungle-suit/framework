<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine;

use Bungle\Framework\Entity\CommonTraits\StatefulInterface;
use Bungle\Framework\Entity\EntityRegistry;
use Bungle\Framework\StateMachine\FSMViewVoter;
use Bungle\Framework\StateMachine\STT\AbstractSTT;
use Bungle\Framework\StateMachine\STT\EntityAccessControlInterface;
use Bungle\Framework\StateMachine\STTLocator\STTLocatorInterface;
use Bungle\Framework\Tests\StateMachine\Entity\Order;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class FSMViewVoterTest extends MockeryTestCase
{
    private FSMViewVoter $voter;
    /** @var Mockery\LegacyMockInterface|Mockery\MockInterface|TokenInterface */
    private $token;
    /** @var STTLocatorInterface|Mockery\LegacyMockInterface|Mockery\MockInterface  */
    private $sttLocator;
    /** @var AbstractSTT|Mockery\LegacyMockInterface|Mockery\MockInterface  */
    private $stt;

    public function init(bool $configAccessSTT = false, bool $hasViewRole = false): void
    {
        $entityRegistry = Mockery::mock(EntityRegistry::class);
        $entityRegistry->allows('getHigh')->with(Order::class)->andReturn('ord');
        $this->sttLocator = Mockery::mock(STTLocatorInterface::class);
        $this->stt = Mockery::mock(AbstractSTT::class);
        if ($configAccessSTT) {
            $this->stt = Mockery::mock(AbstractSTT::class, EntityAccessControlInterface::class);
        }
        $this->sttLocator->allows('getSTTForClass')->with(Order::class)->andReturn($this->stt);
        $this->voter = new FSMViewVoter($this->sttLocator, $entityRegistry);

        $this->token = Mockery::mock(TokenInterface::class);
        if ($hasViewRole) {
            $this->token->allows('getRoleNames')->andReturn(['ROLE_ord_view', 'ROLE_ord_other']);
        } else {
            $this->token->allows('getRoleNames')->andReturn(['ROLE_ord_other']);
        }
    }

    public function testSupports(): void
    {
        self::init();
        // not support unless subject is Stateful
        self::assertEquals(Voter::ACCESS_ABSTAIN, $this->voter->vote($this->token, 33, ['view']));
    }

    public function testNoViewRole(): void
    {
        self::init();
        $obj = new Order;
        self::assertEquals(Voter::ACCESS_DENIED, $this->voter->vote($this->token, $obj, ['view']));
    }

    public function testHasViewRole(): void
    {
        self::init(false, true);
        $obj = new Order;
        self::assertEquals(Voter::ACCESS_GRANTED, $this->voter->vote($this->token, $obj, ['view']));
    }

    public function testViewEntityAccessControl(): void
    {
        self::init(true, true);

        $allows = fn (StatefulInterface $v) => true;
        $reject = fn(StatefulInterface $v) => false;
        $obj = new Order();
        $this->stt->expects('canAccess')->with()->andReturn([$allows, $allows]);
        self::assertEquals(Voter::ACCESS_GRANTED, $this->voter->vote($this->token, $obj, ['view']));

        $this->stt->expects('canAccess')->with()->andReturn([$allows, $reject, $allows]);
        self::assertEquals(Voter::ACCESS_DENIED, $this->voter->vote($this->token, $obj, ['view']));
    }
}
