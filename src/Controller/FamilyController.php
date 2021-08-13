<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractBaseController;
use App\Entity\Family;
use App\Entity\PackageStudentPeriod;
use App\Entity\Student;
use App\Exception\InvalidArgumentException;
use App\Form\FamilyType;
use App\Form\PackageStudentPeriodType;
use App\Form\StudentType;
use App\Manager\FamilyManager;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Family controller.
 *
 * @Route("/family")
 */
class FamilyController extends AbstractBaseController
{
    /**
     * Lists all family entities.
     *
     * @Route("", methods={"GET"})
     */
    public function index()
    {
        /** @var Family[] $families */
        $families = $this->getDoctrine()->getManager()
            ->getRepository(Family::class)
            ->findBy(['enable' => true]);

        return $this->render('family/index.html.twig', [
            'families' => $families,
        ]);
    }

    /**
     * Creates a new family entity.
     *
     * @Route("/new", methods={"GET"})
     *
     * @return Response
     */
    public function new(Request $request)
    {
        $family = new Family();
        $form = $this->createCreateForm($request, $family);

        return $this->render('family/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to create a Grade entity.
     *
     * @return FormInterface
     */
    private function createCreateForm(Request $request, Family $family)
    {
        $form = $this->createForm(FamilyType::class, $family, [
            'action' => $this->generateUrl('app_family_create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'form.button.create'])
            ->handleRequest($request);

        return $form;
    }

    /**
     * Creates a new family entity.
     *
     * @Route("/create", methods={"POST"})
     *
     * @return RedirectResponse|Response
     */
    public function create(Request $request)
    {
        $family = new Family();
        $form = $this->createCreateForm($request, $family);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $family->setAuthor($this->getUser())
                ->setEnable(true)
                ->setGenders();

            $em->persist($family);
            $em->flush();

            $this->addFlash('success', 'The Family has been created.');

            return $this->redirectToRoute('app_family_show', ['id' => $family->getId()]);
        }

        return $this->render('family/new.html.twig', [
            'family' => $family,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a family entity.
     *
     * @Route("/show/{id}", methods={"GET"})
     *
     * @return Response
     *
     * @throws InvalidArgumentException
     */
    public function show(Family $family, FamilyManager $manager)
    {
        $student = new Student();
        $student->getPerson()->setFamily($family);
        $formStudent = $this->createForm(StudentType::class, $student, [
            'action' => $this->generateUrl('app_api_student_create'),
        ]);

        $packageStudentPeriod = new PackageStudentPeriod();
        $packageStudentPeriod->setPeriod($this->getEntityPeriod());
        $formPackage = $this->createForm(PackageStudentPeriodType::class, $packageStudentPeriod, [
            'action' => $this->generateUrl('app_api_package_student_period_create'),
        ]);

        $persons = $manager->getPersons($family, $this->getPeriod());
        //$packages = $manager->getPackages($persons, $this->getPeriod());

        return $this->render('family/show.html.twig', [
            'family' => $family,
            'persons' => $persons,
            'period' => $this->getPeriod(),
            'formStudent' => $formStudent->createView(),
            'formPackage' => $formPackage->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing family entity.
     *
     * @Route("/edit/{id}", methods={"GET"})
     *
     * @return Response
     */
    public function edit(Request $request, Family $family)
    {
        $editForm = $this->createEditForm($request, $family);

        return $this->render('family/edit.html.twig', [
            'family' => $family,
            'form' => $editForm->createView(),
        ]);
    }

    /**
     * Creates a form to create a Grade entity.
     *
     * @return FormInterface
     */
    private function createEditForm(Request $request, Family $family)
    {
        $form = $this->createForm(FamilyType::class, $family, [
            'action' => $this->generateUrl('app_family_update', ['id' => $family->getId()]),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'form.button.edit'])
            ->handleRequest($request);

        return $form;
    }

    /**
     * Displays a form to edit an existing family entity.
     *
     * @Route("/{id}/update", methods={"POST"})
     *
     * @return RedirectResponse|Response
     */
    public function update(Request $request, Family $family)
    {
        $editForm = $this->createEditForm($request, $family);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('app_family_show', ['id' => $family->getId()]);
        }

        return $this->render('family/edit.html.twig', [
            'family' => $family,
            'form' => $editForm->createView(),
        ]);
    }

    /**
     * Deletes a family entity.
     *
     * @Route("/delete/{id}", methods={"DELETE"})
     *
     * @return RedirectResponse
     */
    public function delete(Request $request, Family $family)
    {
        $form = $this->createDeleteForm($family);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($family);
            $em->flush();
        }

        return $this->redirectToRoute('app_family_index');
    }

    /**
     * Creates a form to delete a family entity.
     *
     * @param Family $family The family entity
     *
     * @return FormInterface
     */
    private function createDeleteForm(Family $family)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_family_delete', ['id' => $family->getId()]))
            ->setMethod(Request::METHOD_DELETE)
            ->getForm()
            ->add('submit', SubmitType::class, ['label' => 'form.button.delete']);
    }
}
