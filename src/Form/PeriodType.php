<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Period;
use App\Form\Type\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PeriodType.
 */
class PeriodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $formBuilder
            ->add('name')
            ->add('begin', DatePickerType::class)
            ->add('end', DatePickerType::class)
            ->add('comment', TextareaType::class, [
                'required' => false,
            ])
            ->add('enable', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'data_class' => Period::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_period';
    }
}
