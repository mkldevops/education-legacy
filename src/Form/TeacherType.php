<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\ClassPeriod;
use App\Entity\Period;
use App\Entity\School;
use App\Entity\Teacher;
use App\Repository\ClassPeriodRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeacherType extends AbstractType
{
    protected readonly School $school;

    protected readonly Period $period;

    public function __construct(
        RequestStack $requestStack,
    ) {
        $this->period = $requestStack->getSession()->get('period')->selected;
        $this->school = $requestStack->getSession()->get('school')->school;
    }

    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $period = $this->period;
        $school = $this->school;

        $formBuilder
            ->add('person', PersonType::class, [
                'label' => false,
            ])
            ->add('grade', null, [
                'required' => false,
                'label' => 'form.label.grade',
            ])
            ->add('classPeriods', EntityType::class, [
                'class' => ClassPeriod::class,
                'choice_label' => 'name',
                'multiple' => 'true',
                'query_builder' => static fn (ClassPeriodRepository $classPeriodRepository): QueryBuilder => $classPeriodRepository->getClassPeriodsQueryBuilder($period, $school),
                'attr' => ['data-toggle' => 'multiselect'],
                'required' => false,
                'label' => 'form.label.class_periods',
            ])
            ->add('enable', CheckboxType::class, [
                'required' => false,
                'label' => 'form.label.enabled',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'data_class' => Teacher::class,
            'translation_domain' => 'teacher',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_teacher';
    }
}
