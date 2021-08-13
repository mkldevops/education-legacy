<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\ClassPeriodStudent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ClassPeriodStudentType.
 */
class ClassPeriodStudentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('period', TextType::class, ['label' => 'Nom'])
            ->add('student', IntegerType::class, ['label' => 'Age minimum'])
            ->add('comment', IntegerType::class, ['label' => 'Age maximum'])
            ->add('professors', TextType::class, ['label' => 'Liste professeurs'])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false])
            ->add('status', CheckboxType::class, ['label' => 'Etat', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ClassPeriodStudent::class,
        ]);
    }

    /**
     * getName.
     */
    public function getBlockPrefix(): string
    {
        return 'app_classperiodstudent';
    }
}
