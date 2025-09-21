<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Family;
use App\Entity\Student;
use App\Form\StudentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('student_form')]
final class StudentFormComponent extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public ?Family $family = null;

    #[LiveProp]
    public bool $showModal = false;

    #[LiveProp]
    public string $successMessage = '';

    #[LiveProp]
    public string $errorMessage = '';

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[LiveAction]
    public function save(): void
    {
        $this->submitForm();

        /** @var Student $student */
        $student = $this->getForm()->getData();

        if ($this->getForm()->isValid()) {
            try {
                // Set the family relationship
                if ($this->family) {
                    $student->getPerson()->setFamily($this->family);
                }

                // Enable the student by default
                $student->setEnable(true);

                $this->entityManager->persist($student->getPerson());
                $this->entityManager->persist($student);
                $this->entityManager->flush();

                $this->successMessage = 'L\'étudiant a été ajouté avec succès.';
                $this->showModal = false;

                // Reset form for next use
                $this->resetForm();
            } catch (\Exception $e) {
                $this->errorMessage = 'Une erreur est survenue lors de l\'enregistrement.';
            }
        } else {
            $this->errorMessage = 'Veuillez corriger les erreurs dans le formulaire.';
        }
    }

    #[LiveAction]
    public function openModal(): void
    {
        $this->showModal = true;
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    #[LiveAction]
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->successMessage = '';
        $this->errorMessage = '';
        $this->resetForm();
    }

    protected function instantiateForm(): FormInterface
    {
        $student = new Student();
        if ($this->family) {
            $student->getPerson()->setFamily($this->family);
        }

        return $this->createForm(StudentType::class, $student);
    }

    private function resetForm(): void
    {
        $student = new Student();
        if ($this->family) {
            $student->getPerson()->setFamily($this->family);
        }
        $this->formValues = [];
    }
}
