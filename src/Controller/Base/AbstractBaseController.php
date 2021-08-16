<?php

declare(strict_types=1);

namespace App\Controller\Base;

use App\Entity\Document;
use App\Entity\Period;
use App\Entity\School;
use App\Exception\AppException;
use App\Exception\InvalidArgumentException;
use App\Model\PeriodsList;
use App\Traits\PeriodManagerTrait;
use App\Traits\SchoolManagerTrait;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Fardus\Traits\Symfony\Manager\LoggerTrait;
use Fardus\Traits\Symfony\Manager\TranslatorTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractBaseController extends AbstractController
{
    use LoggerTrait;
    use TranslatorTrait;
    use PeriodManagerTrait;
    use SchoolManagerTrait;

    protected function getEntitySchool(): School
    {
        return $this->getRepository(School::class)
            ->find($this->getSchool()->getId());
    }

    protected function getRepository(string $repository): ObjectRepository
    {
        return $this->getManager()->getRepository($repository);
    }

    protected function getManager(): ObjectManager
    {
        return $this->getDoctrine()->getManager();
    }

    protected function getSchool(): ?\App\Entity\School
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
     * @throws InvalidArgumentException
     */
    protected function getDocument(int $id): ?Document
    {
        if (empty($id)) {
            throw new InvalidArgumentException('Id document is empty');
        }

        $document = $this->getRepository(Document::class)->find($id);

        return $document instanceof Document ? $document : null;
    }
}
