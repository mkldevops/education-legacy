<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\StudentComment;
use App\Form\Type\CkeditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StudentCommentSimpleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('text', CkeditorType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'ckeditor basic',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    StudentComment::COMMENT_APPRECIATION => 'Appreciation',
                    StudentComment::COMMENT_INFORMATION => 'Information',
                    StudentComment::COMMENT_WARNING => 'Avertissement',
                    StudentComment::COMMENT_ALERT => 'Alerte',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StudentComment::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_studentcomment';
    }
}
