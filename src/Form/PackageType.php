<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Package;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PackageType.
 */
class PackageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('price')
            ->add('description')
            ->add('enable', ChoiceType::class, [
                'required' => true,
                'empty_data' => 0,
                'choices' => ['form.status_desactivate' => 0, 'form.status_activate' => 1],
            ])
            ->add('school');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Package::class,
            'translation_domain' => 'package',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_package';
    }
}
