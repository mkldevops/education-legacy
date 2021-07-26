<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of CkeditorType.
 *
 * @author fahari
 */
class CkeditorType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => ['class' => 'ckeditor'],
        ]);
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return TextareaType::class;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return CkeditorType::class;
    }
}
