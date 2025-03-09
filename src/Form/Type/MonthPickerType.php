<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of MonthPickerType.
 *
 * @author fahari
 */
class MonthPickerType extends AbstractType
{
    /**
     * configureOptions.
     */
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'widget' => 'single_text',
            'attr' => ['class' => 'monthpicker'],
            'html5' => false,
        ]);
    }

    /**
     * Get Parent.
     */
    public function getParent(): string
    {
        return DateType::class;
    }

    /**
     * Get Name.
     */
    public function getBlockPrefix(): string
    {
        return self::class;
    }
}
