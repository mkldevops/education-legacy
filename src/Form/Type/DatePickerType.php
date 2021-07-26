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
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'widget' => 'single_text',
            'attr' => ['class' => 'datepicker'],
            'format' => 'dd/MM/yyyy',
            'html5' => false,
        ]);
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return DateType::class;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return DatePickerType::class;
    }
}
