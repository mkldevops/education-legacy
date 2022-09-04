<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\School;
use App\Entity\User;
use App\Exception\SchoolException;
use App\Model\SchoolList;
use App\Repository\SchoolRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class SchoolManager implements SchoolManagerInterface
{
    public function __construct(
        private readonly SchoolRepository $repository,
        private readonly SessionInterface $session,
        private readonly FlashBagInterface $flashBag,
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
        if (!$this->session->has('school') || !$this->session->get('school') instanceof SchoolList) {
            $this->setSchoolsOnSession();
        }

        $schoolList = $this->session->get('school');
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
        $list = $user->getSchoolAccessRight() ?? new ArrayCollection();

        if ($list->isEmpty()) {
            $school = $this->repository->findOneBy([], ['principal' => 'DESC']);
            if (null !== $school) {
                $user = $user->addSchoolAccessRight($school);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
            } else {
                $msg = $this->translator->trans('school.not_found', [], 'user');
                $this->flashBag->add('error', $msg);
                $this->logger->error(__FUNCTION__.' Not found school');

                return false;
            }
        }

        /** @var School $school */
        $school = $list->current();
        $this->session->set('school', new SchoolList($list->toArray(), $school));

        return true;
    }

    /**
     * @throws SchoolException
     */
    public function switch(School $school): void
    {
        $schoolList = $this->session->get('school');
        if (!$schoolList instanceof SchoolList) {
            throw new SchoolException('Error on session school list');
        }

        $schoolList->selected = $school;
        $this->session->set('school', $schoolList);
        $this->session->save();
    }
}
