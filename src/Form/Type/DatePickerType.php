<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of DatePickerType.
 *
 * @author fahari
 */
class DatePickerType extends AbstractType
{
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'widget' => 'single_text',
            'attr' => ['class' => 'datepicker'],
            'format' => 'yyyy-MM-dd',
            'html5' => true,
        ]);
    }

    public function getParent(): string
    {
        return DateType::class;
    }

    public function getBlockPrefix(): string
    {
        return self::class;
    }
}
