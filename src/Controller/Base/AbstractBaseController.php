<?php

declare(strict_types=1);

namespace App\Controller\Base;

use App\Entity\Document;
use App\Entity\Period;
use App\Entity\School;
use App\Exception\AppException;
use App\Exception\EntityRepositoryNotFoundException;
use App\Exception\InvalidArgumentException;
use App\Model\PeriodsList;
use App\Traits\PeriodManagerTrait;
use App\Traits\SchoolManagerTrait;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Fardus\Traits\Symfony\Controller\ResponseTrait;
use Fardus\Traits\Symfony\Manager\LoggerTrait;
use Fardus\Traits\Symfony\Manager\TranslatorTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractBaseController extends AbstractController
{
    use LoggerTrait;
    use TranslatorTrait;
    use PeriodManagerTrait;
    use SchoolManagerTrait;
    use ResponseTrait;

    /**
     * @throws AppException
     */
    protected function getEntitySchool(): School
    {
        return $this->schoolManager->getEntitySchool();
    }

    protected function getRepository(string $repository): ObjectRepository
    {
        return $this->getManager()->getRepository($repository);
    }

    protected function getManager(): ObjectManager
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @throws AppException
     */
    protected function getSchool(): School
    {
        return $this->schoolManager->getSchool();
    }

    /**
     * @throws AppException
     * @throws InvalidArgumentException
     */
    protected function getEntityPeriod(string $type = 'selected'): Period
    {
        if (!in_array($type, ['selected', 'current', 'list'], true)) {
            throw new InvalidArgumentException('Not found the type to Period search');
        }

        $typePeriod = $this->getPeriod($type);

        return $this->periodManager->findPeriod($typePeriod->getId());
    }

    /**
     * @throws AppException
     * @throws InvalidArgumentException
     */
    protected function getPeriod(string $type = PeriodsList::PERIOD_SELECTED): Period
    {
        if (!property_exists(PeriodsList::class, $type)) {
            throw new InvalidArgumentException('Not found the type to Period search');
        }

        if (empty($this->get('session')->get('period'))) {
            $this->periodManager->setPeriodsOnSession();
        }

        return $this->get('session')->get('period')->{$type};
    }

    /**
     * @throws InvalidArgumentException|EntityRepositoryNotFoundException
     */
    protected function getDocument(int $id): Document
    {
        if (empty($id)) {
            throw new InvalidArgumentException('Id document is empty');
        }

        $document = $this->getRepository(Document::class)->find($id);

        if(!$document instanceof Document) {
            throw new EntityRepositoryNotFoundException('Not found document with id '.$id);
        }

        return $document;
    }
}
