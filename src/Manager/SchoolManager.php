<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\School;
use App\Exception\AppException;
use App\Model\SchoolList;
use App\Repository\SchoolRepository;
use App\Services\AbstractFullService;
use Doctrine\Common\Collections\ArrayCollection;
use Fardus\Traits\Symfony\Manager\SessionTrait;

class SchoolManager extends AbstractFullService
{
    use SessionTrait;

    public function __construct(private SchoolRepository $repository)
    {
    }

    /**
     * @throws AppException
     */
    public function getEntitySchool(): ?\App\Entity\School
    {
        return $this->repository->find($this->getSchool()->getId());
    }

    /**
     * @throws AppException
     */
    public function getSchool(): ?School
    {
        if (!$this->session->has('school')
            || !$this->session->get('school') instanceof SchoolList) {
            dump('ici');
            $this->setSchoolsOnSession();
        }

        $schoolList = $this->session->get('school');
        if (!$schoolList instanceof SchoolList) {
            throw new AppException('Error on session school list');
        }

        return $schoolList->selected;
    }

    public function setSchoolsOnSession(): bool
    {
        $list = $this->user?->getSchoolAccessRight() ?? new ArrayCollection();

        if ($list->isEmpty()) {
            $school = $this->repository->findOneBy([], ['principal' => 'DESC']);
            if (null !== $school) {
                $this->user->addSchoolAccessRight($school);
                $this->entityManager->persist($this->user);
                $this->entityManager->flush();
            } else {
                $msg = $this->trans('school.not_found', [], 'user');
                $this->session->getFlashBag()->add('error', $msg);
                $this->logger->error(__FUNCTION__ . ' Not found school');

                return false;
            }
        }

        $school = $list->current();
        $this->session->set('school', new SchoolList($list->toArray(), $school));

        return true;
    }

    /**
     * @throws AppException
     */
    public function switch(School $school): void
    {
        $schoolList = $this->session->get('school');
        if (!$schoolList instanceof SchoolList) {
            throw new AppException();
        }

        $schoolList->selected = $school;
        $this->session->set('school', $schoolList);
        $this->session->save();
    }
}
