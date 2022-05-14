<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Ent;

use Bungle\Framework\Ent\BasalInfoService;
use Bungle\Framework\Ent\ObjectName;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class BasalInfoServiceTest extends MockeryTestCase
{
    private Mockery\LegacyMockInterface|EntityManagerInterface|Mockery\MockInterface $em;
    private Mockery\LegacyMockInterface|Mockery\MockInterface|Security $security;
    private BasalInfoService $basal;
    private Mockery\LegacyMockInterface|ObjectName|Mockery\MockInterface $objectName;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em = Mockery::mock(EntityManagerInterface::class);
        $this->security = Mockery::mock(Security::class);
        $this->objectName = Mockery::mock(ObjectName::class);
        $this->basal = new BasalInfoService($this->security, $this->em, $this->objectName);
    }

    public function testNoCurrentUserSafe(): void
    {
        $this->security->expects('getUser')->with()->andReturnNull();

        self::assertNull($this->basal->currentUserOrNull());
    }

    public function testNoCurrentUserException(): void
    {
        $this->security->expects('getUser')->with()->andReturnNull();

        $this->expectExceptionMessage('No Current User');
        $this->expectException(LogicException::class);

        $this->basal->currentUser();
    }

    public function testCurrentUserNotNull(): void
    {
        $u = Mockery::mock(UserInterface::class);
        $this->security->allows('getUser')->with()->andReturn($u);
        self::assertSame($u, $this->basal->currentUserOrNull());
        self::assertSame($u, $this->basal->currentUser());
    }

    public function testLoadEntity(): void
    {
        $order = new class() {
        };
        $cls = get_class($order);
        $this->em->expects('find')->with($cls, '1234')->andReturn($order);

        self::assertSame($order, $this->basal->loadEntity($cls, '1234'));
    }

    public function testLoadEntityNotFound(): void
    {
        $order = new class() {
        };
        $cls = get_class($order);
        $this->objectName->expects('getName')->with($cls)->andReturn('Order');
        $this->expectExceptionMessage('Order1234不存在');
        $this->expectException(RuntimeException::class);
        $this->em->expects('find')->with($cls, '1234')->andReturn(null);

        $this->basal->loadEntity($cls, '1234');
    }

    public function testToday(): void
    {
        $d = $this->basal->today();
        self::assertEquals('00:00:00.000000', $d->format('H:i:s.u'));
    }

    public function testIsImpersonator(): void
    {
        $this->security->expects('isGranted')->with(AuthenticatedVoter::IS_IMPERSONATOR)->andReturn(true);
        self::assertTrue($this->basal->isImpersonator());
    }
}
