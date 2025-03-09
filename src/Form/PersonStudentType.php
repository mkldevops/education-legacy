<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Family;
use App\Entity\Person;
use App\Form\Type\DatePickerType;
use App\Repository\FamilyRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PersonType.
 */
class PersonStudentType extends PersonSimpleType
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        parent::buildForm($formBuilder, $options);

        $formBuilder
            ->add('gender', ChoiceType::class, [
                'choices' => ['form.gender.choices.male' => Person::GENDER_MALE, 'form.gender.choices.female' => Person::GENDER_FEMALE],
                'label' => 'form.gender.label',
            ])
            ->add('birthday', DatePickerType::class, [
                'label' => 'form.birthday.label',
                'required' => true,
                'format' => 'dd/mm/yyyy',
                'placeholder' => 'form.birthday.placeholder',
            ])
            ->add('birthplace', null, [
                'label' => 'form.birthplace.label',
                'required' => true,
                'attr' => [
                    'placeholder' => 'form.birthplace.placeholder',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'form.email.label',
                'required' => false,
                'attr' => [
                    'placeholder' => 'form.email.placeholder',
                ],
            ])
            ->add('family', EntityType::class, [
                'class' => Family::class,
                'required' => false,
                'label' => 'form.family.label',
                'query_builder' => static fn (FamilyRepository $familyRepository): QueryBuilder => $familyRepository->createQueryBuilder('f')
                    ->where('f.enable = 1'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
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
        return 'app_person_student';
    }
}
