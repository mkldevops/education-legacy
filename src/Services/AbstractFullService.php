<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\School;
use App\Entity\User;
use App\Exception\SchoolException;
use App\Trait\PeriodManagerTrait;
use Fardus\Traits\Symfony\Manager\EntityManagerTrait;
use Fardus\Traits\Symfony\Manager\LoggerTrait;
use Fardus\Traits\Symfony\Manager\TranslatorTrait;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractFullService
{
    use EntityManagerTrait;
    use LoggerTrait;
    use PeriodManagerTrait;
    use TranslatorTrait;

    protected ?User $user = null;

    #[Required]
    public function setUserWithToken(Security $security): void
    {
        if (!($user = $security->getUser()) instanceof \Symfony\Component\Security\Core\User\UserInterface) {
            return;
        }

        if (!$user instanceof User) {
            return;
        }

        $this->user = $user;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @throws SchoolException
     */
    public function findSchool(int $id): School
    {
        $school = $this->entityManager
            ->getRepository(School::class)
            ->find($id)
        ;

        if (!$school instanceof School) {
            throw new SchoolException();
        }

        return $school;
    }
}
