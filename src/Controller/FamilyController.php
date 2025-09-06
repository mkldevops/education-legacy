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

class FamilyController extends AbstractController
{
    #[Route(path: '/family', methods: ['GET'])]
    public function index(FamilyRepository $familyRepository): Response
    {
        $families = $familyRepository->findBy(['enable' => true]);

        return $this->render('family/index.html.twig', [
            'families' => $families,
        ]);
    }

    #[Route(path: '/family/new', methods: ['GET'])]
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
    #[Route(path: '/family/create', methods: ['POST'])]
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
    #[Route(path: '/family/show/{id}', methods: ['GET'])]
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

    #[Route(path: '/family/edit/{id}', methods: ['GET'])]
    public function edit(Request $request, Family $family): Response
    {
        $editForm = $this->createEditForm($request, $family);

        return $this->render('family/edit.html.twig', [
            'family' => $family,
            'form' => $editForm->createView(),
        ]);
    }

    /**
     * Processes the edit form for a Family: on valid submission persists changes and redirects to the family's show page; otherwise re-renders the edit form.
     *
     * If the submitted edit form is valid this method calls the entity manager's flush to persist changes and returns a RedirectResponse to the family's show route. If the form is not submitted or is invalid it returns a Response rendering the edit template with the form view.
     *
     * @return RedirectResponse|Response Redirects to the family's show page on success, or renders the edit page on failure.
     */
    #[Route(path: '/family/{id}/update', methods: ['POST'])]
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

    /**
     * Display a confirmation page for deleting a Family and perform the deletion on POST.
     *
     * If the submitted confirmation form is valid, the Family entity is removed and the EntityManager is flushed,
     * a success flash message is added, and the user is redirected to the family index. Otherwise the delete
     * confirmation view is rendered.
     *
     * @return RedirectResponse|Response Redirects to the family index after successful deletion, or renders the confirmation page.
     */
    #[Route(path: '/family/delete/{id}', methods: ['GET', 'POST'])]
    public function delete(Request $request, Family $family, EntityManagerInterface $entityManager): RedirectResponse|Response
    {
        $form = $this->createDeleteForm($family);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->remove($family);
            $entityManager->flush();

            $this->addFlash('success', 'The Family has been deleted.');

            return $this->redirectToRoute('app_family_index');
        }

        return $this->render('family/delete.html.twig', [
            'family' => $family,
            'delete_form' => $form,
        ]);
    }

    /**
     * Create and handle the "create family" form.
     *
     * Builds a FamilyType form for the given Family, sets its action to the family creation route
     * with POST method, adds a submit button labeled `form.button.create`, and processes the provided Request
     * (binding submitted data and validation state).
     *
     * @return FormInterface The prepared form instance (already handled against the request).
     */
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

    /**
     * Create a delete confirmation form for a Family.
     *
     * The form submits via POST to the `app_family_delete` route for the given family's id
     * and contains a single submit button labeled `form.button.delete`.
     *
     * @param Family $family The family whose id is used to build the delete route.
     * @return FormInterface The configured delete confirmation form.
     */
    private function createDeleteForm(Family $family): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_family_delete', ['id' => $family->getId()]))
            ->setMethod(Request::METHOD_POST)
            ->getForm()
            ->add('submit', SubmitType::class, ['label' => 'form.button.delete'])
        ;
    }
}
