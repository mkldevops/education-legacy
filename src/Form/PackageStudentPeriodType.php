<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Package;
use App\Entity\PackageStudentPeriod;
use App\Entity\School;
use App\Repository\PackageRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Service\Attribute\Required;

class PackageStudentPeriodType extends AbstractType
{
    protected School $school;

    #[Required]
    public function setSchoolBySession(RequestStack $requestStack): void
    {
        $this->school = $requestStack->getSession()->get('school')->selected;
    }

    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $formBuilder
            ->add('package', EntityType::class, [
                'class' => Package::class,
                'choice_label' => 'NameWithPrice',
                'query_builder' => fn (PackageRepository $packageRepository): QueryBuilder => $packageRepository->getAvailable($this->school),
            ])
            ->add('student')
            ->add('discount', MoneyType::class)
            ->add('comment', TextareaType::class, ['required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'data_class' => PackageStudentPeriod::class,
            'translation_domain' => 'package_period_student',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_package_student_period';
    }
}
