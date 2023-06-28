<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\ClassPeriod;
use App\Entity\Course;
use App\Entity\Teacher;
use App\Fetcher\SessionFetcher;
use App\Form\Type\CkeditorType;
use App\Form\Type\DatePickerType;
use App\Form\Type\TimePickerType;
use App\Repository\ClassPeriodRepository;
use App\Repository\TeacherRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CourseType extends AbstractType
{
    public function __construct(
        private readonly SessionFetcher $sessionFetcher,
        RequestStack $requestStack,
        protected ?int $classPeriodId = null,
    ) {
        $this->classPeriodId = $requestStack->getCurrentRequest()?->get('class_period', null);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DatePickerType::class)
        ;

        if (!empty($this->classPeriodId)) {
            $builder
                ->add('classPeriod', HiddenType::class, ['data' => $this->classPeriodId])
            ;
        } else {
            $builder
                ->add('classPeriod', EntityType::class, [
                    'label' => 'form.label.class_period',
                    'class' => ClassPeriod::class,
                    'choice_label' => 'name',
                    'query_builder' => fn (ClassPeriodRepository $er) => $er->getClassPeriodsQueryBuilder(
                        $this->sessionFetcher->getPeriodOnSession(),
                        $this->sessionFetcher->getSchoolOnSession()
                    ),
                ])
            ;
        }

        $builder
            ->add('teachers', EntityType::class, [
                'label' => 'form.label.teacher',
                'class' => Teacher::class,
                'choice_label' => 'name',
                'query_builder' => fn (TeacherRepository $er) => $er->getAvailablesQB($this->sessionFetcher->getSchoolOnSession()),
                'multiple' => true,
                'attr' => ['data-role' => 'multiselect'],
            ])
            ->add('hourBegin', TimePickerType::class, ['label' => 'form.label.hour_begin'])
            ->add('hourEnd', TimePickerType::class, ['label' => 'form.label.hour_end'])
            ->add('text', CkeditorType::class, ['label' => 'form.label.text'])
            ->add('comment', TextareaType::class, ['label' => 'form.label.comment'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
            'translation_domain' => 'course',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_course';
    }
}
