<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\School;
use App\Entity\User;
use App\Exception\SchoolException;
use App\Model\SchoolList;
use App\Repository\SchoolRepository;
use App\Traits\RequestStackTrait;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class SchoolManager implements SchoolManagerInterface
{
    use RequestStackTrait;

    public function __construct(
        private readonly SchoolRepository $repository,
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws SchoolException
     */
    public function getEntitySchool(): School
    {
        return $this->getEntitySchoolOnSession();
    }

    /**
     * @throws SchoolException
     */
    public function getEntitySchoolOnSession(): School
    {
        $school = $this->repository->find($this->getSchool()->getId());

        if (!$school instanceof School) {
            throw new SchoolException('not found a School selected');
        }

        return $school;
    }

    /**
     * @throws SchoolException
     */
    public function getSchool(): School
    {
        return $this->getSchoolOnSession();
    }

    /**
     * @throws SchoolException
     */
    public function getSchoolOnSession(): School
    {
        if (!$this->requestStack->getSession()->has('school') || !$this->requestStack->getSession()->get('school') instanceof SchoolList) {
            $this->setSchoolsOnSession();
        }

        $schoolList = $this->requestStack->getSession()->get('school');
        if (!$schoolList instanceof SchoolList) {
            throw new SchoolException('Error on session school list');
        }

        if (!$schoolList->selected instanceof School) {
            throw new SchoolException('not found a School selected');
        }

        return $schoolList->selected;
    }

    public function setSchoolsOnSession(): bool
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if ($user->getSchoolAccessRight()->isEmpty()) {
            $school = $this->repository->findOneBy([], ['principal' => 'DESC']);
            if ($school instanceof \App\Entity\School) {
                $user = $user->addSchoolAccessRight($school);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
            } else {
                $msg = $this->translator->trans('school.not_found', [], 'user');
                $this->getFlashBag()->add('error', $msg);
                $this->logger->error(__FUNCTION__.' Not found school');

                return false;
            }
        }

        /** @var School $school */
        $school = $user->getSchoolAccessRight()->current();
        $this->requestStack->getSession()->set('school', new SchoolList($user->getSchoolAccessRight()->toArray(), $school));

        return true;
    }

    /**
     * @throws SchoolException
     */
    public function switch(School $school): void
    {
        $schoolList = $this->getSession()->get('school');
        if (!$schoolList instanceof SchoolList) {
            throw new SchoolException('Error on session school list');
        }

        $schoolList->selected = $school;
        $this->getSession()->set('school', $schoolList);
        $this->getSession()->save();
    }
}
