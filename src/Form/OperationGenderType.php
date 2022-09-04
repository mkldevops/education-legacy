<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\OperationGender;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OperationGenderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, ['label' => 'form.name'])
            ->add('code', null, ['label' => 'form.code'])
            ->add('enable', null, ['label' => 'form.enable'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OperationGender::class,
            'translation_domain' => 'operationgender',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_operationgender';
    }
}
