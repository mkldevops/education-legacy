<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Family;
use App\Entity\PackageStudentPeriod;
use App\Entity\Student;
use App\Exception\AppException;
use App\Exception\InvalidArgumentException;
use App\Form\FamilyType;
use App\Form\PackageStudentPeriodType;
use App\Form\StudentType;
use App\Manager\FamilyManager;
use App\Manager\Interfaces\FamilyManagerInterface;
use App\Manager\PeriodManager;
use App\Repository\FamilyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/family')]
class FamilyController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function index(FamilyRepository $familyRepository): Response
    {
        $families = $familyRepository->findBy(['enable' => true]);

        return $this->render('family/index.html.twig', [
            'families' => $families,
        ]);
    }

    #[Route(path: '/new', methods: ['GET'])]
    public function new(Request $request): Response
    {
        $family = new Family();
        $form = $this->createCreateForm($request, $family);

        return $this->render('family/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws AppException
     */
    #[Route(path: '/create', methods: ['POST'])]
    public function create(Request $request, FamilyManagerInterface $familyManager): RedirectResponse|Response
    {
        $family = new Family();
        $form = $this->createCreateForm($request, $family);
        if ($familyManager->persistData($family, $form)) {
            $this->addFlash('success', 'The Family has been created.');

            return $this->redirectToRoute('app_family_show', ['id' => $family->getId()]);
        }

        return $this->render('family/new.html.twig', [
            'family' => $family,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws InvalidArgumentException
     * @throws AppException
     */
    #[Route(path: '/show/{id}', methods: ['GET'])]
    public function show(
        Family $family,
        FamilyManager $familyManager,
        PeriodManager $periodManager,
    ): Response {
        $student = new Student();
        $student->getPerson()?->setFamily($family);
        $formStudent = $this->createForm(StudentType::class, $student, [
            'action' => $this->generateUrl('app_api_student_create'),
        ]);
        $packageStudentPeriod = new PackageStudentPeriod();
        $packageStudentPeriod->setPeriod($periodManager->getEntityPeriodOnSession());

        $formPackage = $this->createForm(PackageStudentPeriodType::class, $packageStudentPeriod, [
            'action' => $this->generateUrl('app_api_package_student_period_create'),
        ]);
        $persons = $familyManager->getPersons($family, $packageStudentPeriod->getPeriod());
        $packages = $familyManager->getPackages($persons, $packageStudentPeriod->getPeriod());

        return $this->render('family/show.html.twig', [
            'family' => $family,
            'persons' => $persons,
            'packages' => $packages,
            'period' => $packageStudentPeriod->getPeriod(),
            'formStudent' => $formStudent->createView(),
            'formPackage' => $formPackage->createView(),
        ]);
    }

    #[Route(path: '/edit/{id}', methods: ['GET'])]
    public function edit(Request $request, Family $family): Response
    {
        $editForm = $this->createEditForm($request, $family);

        return $this->render('family/edit.html.twig', [
            'family' => $family,
            'form' => $editForm->createView(),
        ]);
    }

    #[Route(path: '/{id}/update', methods: ['POST'])]
    public function update(Request $request, Family $family, EntityManagerInterface $entityManager): RedirectResponse|Response
    {
        $editForm = $this->createEditForm($request, $family);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_family_show', ['id' => $family->getId()]);
        }

        return $this->render('family/edit.html.twig', [
            'family' => $family,
            'form' => $editForm->createView(),
        ]);
    }

    #[Route(path: '/delete/{id}', methods: ['DELETE'])]
    public function delete(Request $request, Family $family, EntityManagerInterface $entityManager): RedirectResponse
    {
        $form = $this->createDeleteForm($family);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->remove($family);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_family_index');
    }

    private function createCreateForm(Request $request, Family $family): FormInterface
    {
        return $this->createForm(FamilyType::class, $family, [
            'action' => $this->generateUrl('app_family_create'),
            'method' => Request::METHOD_POST,
        ])
            ->add('submit', SubmitType::class, ['label' => 'form.button.create'])
            ->handleRequest($request)
        ;
    }

    private function createEditForm(Request $request, Family $family): FormInterface
    {
        $form = $this->createForm(FamilyType::class, $family, [
            'action' => $this->generateUrl('app_family_update', ['id' => $family->getId()]),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'form.button.edit'])
            ->handleRequest($request)
        ;

        return $form;
    }

    private function createDeleteForm(Family $family): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_family_delete', ['id' => $family->getId()]))
            ->setMethod(Request::METHOD_DELETE)
            ->getForm()
            ->add('submit', SubmitType::class, ['label' => 'form.button.delete'])
        ;
    }
}
