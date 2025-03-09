<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of MonthPickerType.
 *
 * @author fahari
 */
class TimePickerType extends AbstractType
{
    /**
     * configureOptions.
     */
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'widget' => 'single_text',
            'attr' => ['class' => 'timepicker'],
        ]);
    }

    /**
     * Get Parent.
     */
    public function getParent(): string
    {
        return TimeType::class;
    }

    /**
     * Get Name.
     */
    public function getBlockPrefix(): string
    {
        return self::class;
    }
}
