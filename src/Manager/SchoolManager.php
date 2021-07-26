<?php

namespace App\Manager;

use App\Entity\Period;
use App\Entity\School;
use App\Exception\AppException;
use App\Exception\PeriodException;
use App\Model\PeriodsList;
use App\Model\SchoolList;
use App\Repository\PeriodRepository;
use App\Repository\SchoolRepository;
use App\Services\AbstractFullService;
use Fardus\Traits\Symfony\Manager\SessionTrait;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SchoolManager extends AbstractFullService
{
    use SessionTrait;
    public ?SchoolRepository $repository = null;

    /**
     * @required
     */
    public function setRepository(?SchoolRepository $repository): void
    {
        $this->repository = $repository;
    }

    public function setSchoolsOnSession(): bool
    {
        $list = $this->user->getSchoolAccessRight();

        if ($this->user->getSchoolAccessRight()->isEmpty()) {
            /* @var $school School */
            $school = $this->repository->findOneBy([], ['principal' => 'DESC']);

            if (!empty($school)) {
                $this->user->addSchoolAccessRight($school);
                $this->entityManager->persist($this->user);
                $this->entityManager->flush();
            } else {
                $msg = $this->trans('school.not_found', [], 'user');
                $this->session->getFlashBag()->add('error', $msg);
                $this->logger->error(__FUNCTION__.' Not found school');
                return false;
            }
        }

        $school = $this->user->getSchoolAccessRight()->current();
        $this->session->set('school', new SchoolList($list->toArray(), $school));
        return true;
    }

    /**
     * @throws AppException
     */
    public function getSchool(): ?School
    {
        if (!$this->session->has('school')) {
            $this->setSchoolsOnSession();
        }

        $schoolList = $this->session->get('school');
        if(!$schoolList instanceof SchoolList) {
            throw new AppException('Error on session school list');
        }
        return $schoolList->selected;
    }

    /**
     * @throws AppException
     */
    public function getEntitySchool(): School
    {
        return $this->repository->find($this->getSchool()->getId());
    }

    /**
     * @throws AppException
     */
    public function switch(School $school) : void
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
