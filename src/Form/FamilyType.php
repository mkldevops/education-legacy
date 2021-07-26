<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Family;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FamilyType.
 */
class FamilyType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('father', PersonSimpleType::class, [
                'label' => 'form.father.label',
                'required' => false,
            ])
            ->add('mother', PersonSimpleType::class, [
                'label' => 'form.mother.label',
                'required' => false,
            ])
            ->add('legalGuardian', PersonSimpleType::class, [
                'label' => 'form.legalGuardian.label',
                'required' => false,
            ])
            ->add('email', null, [
                'label' => 'form.email.label',
            ])
            ->add('numberChildren', null, [
                'label' => 'form.numberChildren.label',
            ])
            ->add('address', null, [
                'label' => 'form.address.label',
            ])
            ->add('city', null, [
                'label' => 'form.city.label',
            ])
            ->add('zip', null, [
                'label' => 'form.zip.label',
            ])
            ->add('personAuthorized', TextareaType::class, [
                'label' => 'form.personAuthorized.label',
                'required' => false,
            ])
            ->add('personEmergency', TextareaType::class, [
                'label' => 'form.personEmergency.label',
                'required' => false,
            ])
            ->add('language', null, [
                'label' => 'form.language.label',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Family::class,
            'translation_domain' => 'family',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'app_family';
    }
}
