<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Meet;
use App\Form\Type\CkeditorType;
use App\Form\Type\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MeetType.
 */
class MeetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('date', DatePickerType::class)
            ->add('subject')
            ->add('text', CkeditorType::class)
            ->add('enable')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Meet::class,
        ]);
    }

    /**
     * getName.
     */
    public function getBlockPrefix(): string
    {
        return 'app_meet';
    }
}
