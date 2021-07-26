<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Person;
use App\Form\Type\DatePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PersonType.
 */
class PersonType extends PersonSimpleType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'form.name.label',
            ])
            ->add('forname', null, [
                'label' => 'form.forname.label',
            ])
            ->add('phone', null, [
                'label' => 'form.phone.label',
            ])
            ->add('email', null, [
                'label' => 'form.email.label',
            ])
            ->add('birthday', DatePickerType::class, [
                'label' => 'form.birthday.label',
                'format' => 'dd/mm/yyyy',
                'required' => false,
            ])
            ->add('birthplace', null, [
                'label' => 'form.birthplace.label',
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => ['masculin' => 'Masculin', 'feminin' => 'Féminin'],
                'label' => 'form.gender.label',
            ])
            ->add('address', null, [
                'label' => 'form.address.label',
            ])
            ->add('zip', null, [
                'label' => 'form.zip.label',
            ])
            ->add('city', null, [
                'label' => 'form.city.label',
            ])
            ->add('pathRedirect', HiddenType::class, [
                'mapped' => false,
                'data' => $options['pathRedirect'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
            'pathRedirect' => null,
            'translation_domain' => 'person',
        ]);
    }

    /**
     * getBlockPrefix.
     */
    public function getBlockPrefix(): string
    {
        return 'app_person';
    }
}
