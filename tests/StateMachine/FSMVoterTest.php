<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine;

use Bungle\Framework\Entity\CommonTraits\StatefulInterface;
use Bungle\Framework\StateMachine\STT\AbstractSTT;
use Bungle\Framework\StateMachine\STT\EntityAccessControlInterface;
use Bungle\Framework\StateMachine\FSMVoter;
use Bungle\Framework\StateMachine\STTLocator\STTLocatorInterface;
use Bungle\Framework\Tests\StateMachine\Entity\Order;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class FSMVoterTest extends MockeryTestCase
{
    private FSMVoter $voter;
    /** @var Mockery\LegacyMockInterface|Mockery\MockInterface|TokenInterface */
    private $token;
    /** @var STTLocatorInterface|Mockery\LegacyMockInterface|Mockery\MockInterface  */
    private $sttLocator;
    /** @var AbstractSTT|Mockery\LegacyMockInterface|Mockery\MockInterface  */
    private $stt;

    public function init(bool $configAccessSTT = false): void
    {
        $this->sttLocator = Mockery::mock(STTLocatorInterface::class);
        $this->stt = Mockery::mock(AbstractSTT::class);
        if ($configAccessSTT) {
            $this->stt = Mockery::mock(AbstractSTT::class, EntityAccessControlInterface::class);
        }
        $this->sttLocator->allows('getSTTForClass')->with(Order::class)->andReturn($this->stt);
        $this->voter = new FSMVoter($this->sttLocator);
        $this->token = Mockery::mock(TokenInterface::class);
    }

    public function testSupports(): void
    {
        self::init();
        // not support unless subject is Stateful
        self::assertEquals(Voter::ACCESS_ABSTAIN, $this->voter->vote($this->token, 33, ['view']));

        $obj = Mockery::mock(StatefulInterface::class);
        // not support unless action is known
        self::assertEquals(Voter::ACCESS_ABSTAIN, $this->voter->vote($this->token, $obj, ['blah']));
    }

    public function testAllowsViewByDefault(): void
    {
        self::init();
        $obj = new Order;
        self::assertEquals(Voter::ACCESS_GRANTED, $this->voter->vote($this->token, $obj, ['view']));
    }

    public function testViewEntityAccessControl(): void
    {
        self::init(true);

        $allows = fn (StatefulInterface $v) => true;
        $reject = fn(StatefulInterface $v) => false;
        $obj = new Order();
        $this->stt->expects('canAccess')->with()->andReturn([$allows, $allows]);
        self::assertEquals(Voter::ACCESS_GRANTED, $this->voter->vote($this->token, $obj, ['view']));

        $this->stt->expects('canAccess')->with()->andReturn([$allows, $reject, $allows]);
        self::assertEquals(Voter::ACCESS_DENIED, $this->voter->vote($this->token, $obj, ['view']));
    }
}
