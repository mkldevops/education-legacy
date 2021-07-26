<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\School;
use App\Entity\User;
use App\Exception\SchoolException;
use App\Traits\PeriodManagerTrait;
use Fardus\Traits\Symfony\Manager\EntityManagerTrait;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractFullService extends AbstractService
{
    use EntityManagerTrait;
    use PeriodManagerTrait;

    protected ?User $user = null;

    #[Required]
    public function setUserWithToken(TokenStorageInterface $token): static
    {
        if (null !== $token->getToken() && $token->getToken()?->getUser() instanceof User) {
            /** @var User $user */
            $user = $token->getToken()?->getUser();
            $this->setUser($user);
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @throws SchoolException
     */
    public function findSchool(int $id): School
    {
        $school = $this->entityManager
            ->getRepository(School::class)
            ->find($id);

        if (!$school instanceof School) {
            throw new SchoolException();
        }

        return $school;
    }
}
