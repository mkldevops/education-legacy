<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('person', PersonType::class, [
                'label' => false,
            ])
            ->add('positionName')
            ->add('enable');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Member::class,
            'translation_domain' => 'member',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_member';
    }
}
