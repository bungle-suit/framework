<?php
declare(strict_types=1);

namespace Bungle\Framework\Ent;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use RuntimeException;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class BasalInfoService
{
    private Security $security;
    protected EntityManagerInterface $em;
    private ObjectName $objectName;

    public function __construct(Security $security, EntityManagerInterface $em, ObjectName $objectName)
    {
        $this->security = $security;
        $this->em = $em;
        $this->objectName = $objectName;
    }

    public function now(): DateTime
    {
        return new DateTime();
    }

    /**
     * Return today without date part, returned in PHP current timezone.
     */
    public function today(): DateTime
    {
        $d = $this->now();
        $d->setTime(0, 0);
        return $d;
    }

    /**
     * Return current logged in user as Entity/User.
     *
     * @throws LogicException if no current user, @see currentUserOrNull()
     */
    public function currentUser(): UserInterface
    {
        $r = $this->currentUserOrNull();
        if (null === $r) {
            throw new LogicException('No Current User');
        }

        return $r;
    }

    /**
     * Return current user null if no logged-in user.
     */
    public function currentUserOrNull(): ?UserInterface
    {
        return $this->security->getUser();
    }

    /**
     * @template T
     * @phpstan-param class-string<T> $cls
     * @param int|string $id
     * @phpstan-return T
     *
     * @throws RuntimeException if the entity object not found.
     */
    public function loadEntity(string $cls, $id)
    {
        $r = $this->em->find($cls, $id);
        if ($r === null) {
            $name = $this->objectName->getName($cls);
            throw new RuntimeException("$name{$id}不存在");
        }

        return $r;
    }

    /**
     * return true if current user is impersonator, i.e. presented to be another user.
     */
    public function isImpersonator(): bool
    {
        return $this->security->isGranted(AuthenticatedVoter::IS_IMPERSONATOR);
    }
}
