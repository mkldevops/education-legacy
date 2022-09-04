<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Family;
use App\Entity\Period;
use App\Entity\Person;
use App\Entity\Student;
use App\Exception\AppException;
use App\Manager\Interfaces\FamilyManagerInterface;
use App\Repository\PackageStudentPeriodRepository;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Security;

class FamilyManager implements FamilyManagerInterface
{
    public function __construct(
        private PersonRepository $personRepository,
        private PackageStudentPeriodRepository $packageStudentPeriodRepository,
        private EntityManagerInterface $entityManager,
        private Security $security,
        private LoggerInterface $logger,
    ) {
    }

    public function getPersons(Family $family, Period $period): array
    {
        return $this->personRepository->getPersonsToFamily($family, $period);
    }

    public function getPackages(array $persons, Period $period): array
    {
        $students = array_map(static fn (Person $person): ?Student => $person->getStudent(), $persons);

        $packages = $this->packageStudentPeriodRepository->findBy(['student' => $students, 'period' => $period]);
        $this->logger->debug(__METHOD__, compact('packages'));

        return $packages;
    }

    /**
     * @throws AppException
     */
    public function persistData(Family $family, FormInterface $form): bool
    {
        if (!$form->isSubmitted()) {
            throw new AppException('The form is not submitted ');
        }

        if (!$form->isValid()) {
            $this->logger->debug(__METHOD__.' Form family invalid', ['errors' => $form->getErrors()]);

            throw new AppException('The form is not valid '.$form->getErrors());
        }

        $family
            ->setName($family->__toString())
            ->setGenders()
            ->setEnable(true)
            ->setAuthor($this->security->getUser())
        ;
        $this->entityManager->persist($family);
        $this->entityManager->flush();

        return true;
    }
}
