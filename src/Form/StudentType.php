<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Grade;
use App\Entity\Student;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StudentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('person', PersonStudentType::class, [
                'label' => false,
            ])
            ->add('grade', EntityType::class, [
                'label' => 'Niveau',
                'class' => Grade::class,
                'choice_label' => 'name',
                'placeholder' => 'form.grade.placeholder',
            ])
            ->add('lastSchool', null, ['label' => 'label.lastSchool', 'required' => false])
            ->add('personAuthorized', TextareaType::class, ['label' => 'label.personAuthorized', 'required' => false])
            ->add('remarksHealth', TextareaType::class, ['label' => 'label.remarksHealth', 'required' => false])
            ->add('letAlone', null, ['label' => 'label.letAlone', 'required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Student::class,
            'translation_domain' => 'student',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_student';
    }
}
