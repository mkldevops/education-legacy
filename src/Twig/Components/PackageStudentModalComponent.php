<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\PackageStudentPeriod;
use App\Entity\Period;
use App\Form\PackageStudentPeriodType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('package_student_modal')]
final class PackageStudentModalComponent extends AbstractController
{
    public ?Period $period = null;

    private ?FormView $formView = null;

    public function getForm(): FormView
    {
        if ($this->formView instanceof FormView) {
            return $this->formView;
        }

        $packageStudentPeriod = new PackageStudentPeriod();
        $packageStudentPeriod->setPeriod($this->period);

        $this->formView = $this->createForm(PackageStudentPeriodType::class, $packageStudentPeriod, [
            'action' => $this->generateUrl('app_api_package_student_period_create'),
        ])->createView();

        return $this->formView;
    }
}
