<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\ClassPeriod;
use App\Entity\Course;
use App\Entity\Period;
use App\Entity\School;
use App\Entity\Teacher;
use App\Form\Type\CkeditorType;
use App\Form\Type\DatePickerType;
use App\Form\Type\TimePickerType;
use App\Repository\ClassPeriodRepository;
use App\Repository\TeacherRepository;
use Psr\Http\Message\RequestInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CourseType.
 */
class CourseType extends AbstractType
{
    protected Session|SessionInterface $session;

    /**
     * @var RequestStack
     */
    protected $request;

    protected ?School $school = null;

    protected ?Period $period = null;

    protected ?int $classPeriodId = null;

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(School $school): self
    {
        $this->school = $school;

        return $this;
    }

    public function getPeriod(): ?Period
    {
        return $this->period;
    }

    public function setPeriod(Period $period): self
    {
        $this->period = $period;

        return $this;
    }

    /**
     * Get ClassPeriodId.
     */
    public function getClassPeriodId(): ?int
    {
        return $this->classPeriodId;
    }

    /**
     * Set ClassPeriodId.
     */
    public function setClassPeriodId(int $classPeriodId): static
    {
        $this->classPeriodId = $classPeriodId;

        return $this;
    }

    public function getSession(): Session
    {
        return $this->session;
    }

    /**
     * @required
     */
    public function setSession(SessionInterface $session): static
    {
        $this->session = $session;

        $this->setPeriod($this->session->get('period')->selected);
        $this->setSchool($this->session->get('school'));

        return $this;
    }

    public function getRequest(): RequestStack
    {
        return $this->request;
    }

    public function setRequest(RequestInterface $request): static
    {
        $this->request = $request;
        $this->setClassPeriodId($this->request->getCurrentRequest()->get('class_period', null));

        return $this;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DatePickerType::class);

        if (!empty($this->classPeriodId)) {
            $builder
                ->add('classPeriod', HiddenType::class, ['data' => $this->classPeriodId]);
        } else {
            $builder
                ->add('classPeriod', EntityType::class, [
                    'label' => 'form.label.class_period',
                    'class' => ClassPeriod::class,
                    'choice_label' => 'name',
                    'query_builder' => fn (ClassPeriodRepository $er) => $er->getClassPeriodsQueryBuilder($this->period, $this->school),
                ]);
        }

        $builder
            ->add('teachers', EntityType::class, [
                'label' => 'form.label.teacher',
                'class' => Teacher::class,
                'choice_label' => 'name',
                'query_builder' => fn (TeacherRepository $er) => $er->getAvailablesQB($this->school),
                'multiple' => true,
                'attr' => ['data-role' => 'multiselect'],
            ])
            ->add('hourBegin', TimePickerType::class, ['label' => 'form.label.hour_begin'])
            ->add('hourEnd', TimePickerType::class, ['label' => 'form.label.hour_end'])
            ->add('text', CkeditorType::class, ['label' => 'form.label.text'])
            ->add('comment', TextareaType::class, ['label' => 'form.label.comment']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
            'translation_domain' => 'course',
        ]);
    }

    /**
     * getName.
     */
    public function getBlockPrefix(): string
    {
        return 'app_course';
    }
}
