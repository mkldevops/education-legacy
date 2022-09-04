<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\PackageStudentPeriod;
use App\Form\PackageStudentPeriodType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/package-student-period')]
class PackageStudentPeriodController extends AbstractController
{
    #[Route(path: '/create', name: 'app_package_student_period_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $packageStudentPeriod = new PackageStudentPeriod();
        $form = $this->createCreateForm($packageStudentPeriod);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($packageStudentPeriod);
            $entityManager->flush();

            $this->addFlash('success', 'The package has been added to student.');

            return $this->redirect($this->generateUrl(
                'app_package_student_period_show',
                ['id' => $packageStudentPeriod->getId()]
            ));
        }

        return $this->render('package_student_period/new.html.twig', [
            'packagestudentperiod' => $packageStudentPeriod,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/new', name: 'app_package_student_period_new', methods: ['GET'])]
    public function new(): Response
    {
        $packageStudentPeriod = new PackageStudentPeriod();
        $form = $this->createCreateForm($packageStudentPeriod);

        return $this->render('package_student_period/new.html.twig', [
            'packagestudentperiod' => $packageStudentPeriod,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a PackageStudentPeriod entity.
     */
    #[Route(path: '/show/{id}', name: 'app_package_student_period_show', methods: ['GET'])]
    public function show(PackageStudentPeriod $packageStudentPeriod): Response
    {
        return $this->render('package_student_period/show.html.twig', [
            'packagestudentperiod' => $packageStudentPeriod,
        ]);
    }

    /**
     * Displays a form to edit an existing PackageStudentPeriod entity.
     */
    #[Route(path: '/edit/{id}', name: 'app_package_student_period_edit', methods: ['GET'])]
    public function edit(PackageStudentPeriod $packageStudentPeriod): Response
    {
        $editForm = $this->createEditForm($packageStudentPeriod);

        return $this->render('package_student_period/edit.html.twig', [
            'packagestudentperiod' => $packageStudentPeriod,
            'edit_form' => $editForm->createView(),
        ]);
    }

    #[Route(path: '/update/{id}', name: 'app_package_student_period_update', methods: ['PUT', 'POST'])]
    public function update(Request $request, PackageStudentPeriod $packageStudentPeriod, EntityManagerInterface $entityManager): Response
    {
        $editForm = $this->createEditForm($packageStudentPeriod);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'The PackageStudentPeriod has been updated.');

            return $this->redirect($this->generateUrl('app_package_student_period_show', ['id' => $packageStudentPeriod->getId()]));
        }

        return $this->render('package_student_period/edit.html.twig', [
            'packagestudentperiod' => $packageStudentPeriod,
            'edit_form' => $editForm->createView(),
        ]);
    }

    #[Route(path: '/delete/{id}', name: 'app_package_student_period_delete', methods: ['GET', 'DELETE'])]
    public function delete(Request $request, PackageStudentPeriod $packageStudentPeriod, EntityManagerInterface $entityManager): Response
    {
        $deleteForm = $this->createDeleteForm($packageStudentPeriod->getId());
        $deleteForm->handleRequest($request);

        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $entityManager->remove($packageStudentPeriod);
            $entityManager->flush();

            $this->addFlash('success', 'The PackageStudentPeriod has been deleted.');

            return $this->redirect($this->generateUrl('app_admin_home'));
        }

        return $this->render('package_student_period/delete.html.twig', [
            'packagestudentperiod' => $packageStudentPeriod,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    private function createCreateForm(PackageStudentPeriod $packageStudentPeriod): FormInterface
    {
        $form = $this->createForm(PackageStudentPeriodType::class, $packageStudentPeriod, [
            'action' => $this->generateUrl('app_package_student_period_create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }

    private function createEditForm(PackageStudentPeriod $packageStudentPeriod): FormInterface
    {
        $form = $this->createForm(PackageStudentPeriodType::class, $packageStudentPeriod, [
            'action' => $this->generateUrl('app_package_student_period_update', ['id' => $packageStudentPeriod->getId()]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->remove('period')
            ->add('submit', SubmitType::class, ['label' => 'Update'])
        ;

        return $form;
    }

    private function createDeleteForm(int $id): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl(
                'app_package_student_period_delete',
                ['id' => $id]
            ))
            ->setMethod(Request::METHOD_DELETE)
            ->add('submit', SubmitType::class, ['label' => 'Delete'])
            ->getForm()
        ;
    }
}
