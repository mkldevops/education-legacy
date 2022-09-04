<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonSimpleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('forname', null, ['label' => 'form.forname.label'])
            ->add('name', null, ['label' => 'form.name.label'])
            ->add('phone', TextType::class, [
                'label' => 'form.phone.label',
                'required' => false,
                'attr' => [
                    'placeholder' => 'form.phone.placeholder',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
            'pathRedirect' => null,
            'translation_domain' => 'person',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_person_family';
    }
}
