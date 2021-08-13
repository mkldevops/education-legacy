<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\ClassPeriod;
use App\Entity\ClassSchool;
use App\Entity\Period;
use App\Entity\School;
use App\Repository\ClassSchoolRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ClassPeriodType.
 */
class ClassPeriodType extends AbstractType
{
    protected ?School $school = null;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('classSchool', EntityType::class, [
                'class' => ClassSchool::class,
                'query_builder' => fn(ClassSchoolRepository $er) => $er->getQBAvailables(),
            ])
            ->add('period', EntityType::class, [
                'class' => Period::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.begin', 'DESC');
                },
            ])
            ->add('comment')
            ->add('status');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ClassPeriod::class,
            'translation_domain' => 'class_period',
        ]);
    }

    /**
     * getName.
     */
    public function getBlockPrefix(): string
    {
        return 'app_classperiod';
    }

    /**
     * Get School.
     */
    public function getSchool(): School
    {
        return $this->school;
    }

    /**
     * Set School.
     */
    public function setSchool(School $school): self
    {
        $this->school = $school;

        return $this;
    }
}
