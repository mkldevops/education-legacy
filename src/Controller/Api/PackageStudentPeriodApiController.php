<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\PackageStudentPeriod;
use App\Exception\AppException;
use App\Exception\InvalidArgumentException;
use App\Exception\PeriodException;
use App\Form\PackageStudentPeriodType;
use App\Manager\PeriodManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/pachage-student-period', options: ['expose' => true])]
class PackageStudentPeriodApiController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private PeriodManager $periodManager,
    ) {
    }

    /**
     * @throws AppException
     * @throws InvalidArgumentException
     * @throws PeriodException
     */
    #[Route(path: '/create', name: 'app_api_package_student_period_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $packageStudentPeriod = (new PackageStudentPeriod())
            ->setPeriod($this->periodManager->getEntityPeriodOnSession())
            ->setDateExpire($this->periodManager->getPeriodsOnSession()->getEnd())
        ;

        $form = $this->createForm(PackageStudentPeriodType::class, $packageStudentPeriod)
            ->handleRequest($request)
        ;

        $packageStudentPeriod->setAmount($packageStudentPeriod->getPackage()?->getPrice());
        $this->persistData($packageStudentPeriod, $form);

        $this->addFlash('success', 'The package of student has been added.');

        return $this->json($packageStudentPeriod);
    }

    /**
     * @throws AppException
     */
    #[Route(path: '/update/{id}', name: 'app_api_package_student_period_update', methods: ['POST', 'PUT'])]
    public function update(Request $request, PackageStudentPeriod $packageStudentPeriod): JsonResponse
    {
        $this->logger->info(__FUNCTION__, ['request' => $request]);
        $form = $this->createForm(PackageStudentPeriodType::class, $packageStudentPeriod)
            ->handleRequest($request)
        ;

        $this->persistData($packageStudentPeriod, $form);

        $this->addFlash('success', sprintf(
            'The package of student %s has been updated.',
            $packageStudentPeriod->getStudent()
        ));

        return $this->json($packageStudentPeriod);
    }

    /**
     * @throws AppException
     */
    private function persistData(PackageStudentPeriod $packageStudentPeriod, FormInterface $form): void
    {
        $this->logger->debug(__METHOD__, ['packageStudentPeriod' => $packageStudentPeriod]);
        if (!$form->isSubmitted()) {
            throw new AppException('The form is not submitted ');
        }

        if (!$form->isValid()) {
            throw new AppException('The form is not valid '.$form->getErrors());
        }

        $packageStudentPeriod
            ->setAuthor($this->getUser())
        ;

        $this->entityManager->persist($packageStudentPeriod);
        $this->entityManager->flush();
    }
}
