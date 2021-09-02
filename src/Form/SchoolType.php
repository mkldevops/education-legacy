<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\School;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SchoolType.
 */
class SchoolType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('director', null, ['label' => 'label.director'])
            ->add('name', null, ['label' => 'label.name'])
            ->add('address', null, ['label' => 'label.address'])
            ->add('zip', null, ['label' => 'label.zip'])
            ->add('city', null, ['label' => 'label.city'])
            ->add('comment', TextareaType::class, [
                'label' => 'label.comment',
                'required' => false,
            ])
            ->add('enable', null, ['label' => 'label.status']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => School::class,
            'translation_domain' => 'school',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_school';
    }
}
