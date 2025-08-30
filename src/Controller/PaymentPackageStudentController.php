<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Family;
use App\Entity\PackageStudentPeriod;
use App\Entity\PaymentPackageStudent;
use App\Entity\Period;
use App\Entity\TypeOperation;
use App\Form\OperationPaymentStudentType;
use App\Form\PaymentPackageStudentType;
use App\Repository\TypeOperationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentPackageStudentController extends AbstractController
{
    #[Route('payment-package-student/create/{id}', name: 'app_payment_package_student_create', methods: ['GET', 'POST'])]
    public function create(Request $request, PackageStudentPeriod $packageStudentPeriod, TypeOperationRepository $typeOperationRepository, EntityManagerInterface $entityManager): Response
    {
        $paymentPackageStudent = new PaymentPackageStudent();
        $form = $this->createCreateForm($paymentPackageStudent, $packageStudentPeriod);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $typeOperation = $typeOperationRepository->findOneBy(['code' => TypeOperation::TYPE_CODE_PAYMENT_PACKAGE_STUDENT]);

            $paymentPackageStudent->setPackageStudentPeriod($packageStudentPeriod);
            $paymentPackageStudent->getOperation()->setTypeOperation($typeOperation);

            if (!$paymentPackageStudent->getOperation()->getDate() instanceof \DateTimeInterface
                && $planned = $paymentPackageStudent->getOperation()->getDatePlanned()) {
                $paymentPackageStudent->getOperation()->setDate($planned);
            }

            $paymentPackageStudent->getOperation()
                ->setName(\sprintf(
                    '%s - %s ',
                    $packageStudentPeriod->getStudent()?->getNameComplete() ?? '',
                    $packageStudentPeriod->getPeriod()?->getName() ?? ''
                ))
            ;

            $entityManager->persist($paymentPackageStudent);
            $entityManager->flush();

            $this->addFlash('success', 'The PaymentPackageStudent has been created.');
        } else {
            $this->addFlash('danger', 'The PaymentPackageStudent form invalid');
        }

        return $this->redirectToRoute('app_student_show', [
            'id' => $packageStudentPeriod->getStudent()?->getId(),
        ]);
    }

    #[Route(path: 'payment-package-student/family/{family}/{period}', name: 'app_payment_package_student_family', methods: ['GET'])]
    public function family(Family $family, Period $period): Response
    {
        $form = $this->createForm(OperationPaymentStudentType::class);

        return $this->render('payment_package_student/family.html.twig', [
            'family' => $family,
            'period' => $period,
            'form' => $form->createView(),
        ]);
    }

    #[Route('payment-package-student/new-student/{id}', name: 'app_payment_package_student_new', methods: ['GET', 'POST'])]
    public function newStudent(PackageStudentPeriod $packageStudentPeriod): Response
    {
        $paymentPackageStudent = new PaymentPackageStudent();
        $form = $this->createCreateForm($paymentPackageStudent, $packageStudentPeriod);

        return $this->render('payment_package_student/newStudent.html.twig', [
            'packageStudentPeriod' => $packageStudentPeriod,
            'paymentPackageStudent' => $paymentPackageStudent,
            'form' => $form->createView(),
        ]);
    }

    private function createCreateForm(
        PaymentPackageStudent $paymentPackageStudent,
        PackageStudentPeriod $packageStudentPeriod
    ): FormInterface {
        $form = $this->createForm(PaymentPackageStudentType::class, $paymentPackageStudent, [
            'action' => $this->generateUrl('app_payment_package_student_create', [
                'id' => $packageStudentPeriod->getId(),
            ]),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }
}
