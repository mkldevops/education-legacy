<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\ClassSchool;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ClassSchoolType.
 */
class ClassSchoolType extends AbstractType
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $formBuilder
            ->add('name', TextType::class, ['label' => 'Nom'])
            ->add('ageMinimum', IntegerType::class, ['label' => 'Age minimum'])
            ->add('ageMaximum', IntegerType::class, ['label' => 'Age maximum'])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false])
            ->add('enable', CheckboxType::class, ['label' => 'Activer', 'required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'data_class' => ClassSchool::class,
            'translation_domain' => 'class_school',
        ]);
    }

    /**
     * getName.
     */
    public function getBlockPrefix(): string
    {
        return 'app_classschool';
    }
}
