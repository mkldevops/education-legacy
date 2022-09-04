<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\ClassPeriod;
use App\Entity\Period;
use App\Entity\School;
use App\Entity\Teacher;
use App\Repository\ClassPeriodRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TeacherType.
 */
class TeacherType extends AbstractType
{
    protected ?School $school = null;
    protected ?Period $period = null;

    public function __construct(SessionInterface $session)
    {
        $this->period = $session->get('period')->selected;
        $this->school = $session->get('school');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $period = $this->period;
        $school = $this->school;

        $builder
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
                'query_builder' => fn (ClassPeriodRepository $cpr) => $cpr->getClassPeriodsQueryBuilder($period, $school),
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Teacher::class,
            'translation_domain' => 'teacher',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_teacher';
    }
}
