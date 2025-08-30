<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\StudentComment;
use App\Form\Type\CkeditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StudentCommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $formBuilder
            ->add('title', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Title comment',
                ],
            ])
            ->add('text', CkeditorType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Write message...',
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

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'data_class' => StudentComment::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_studentcomment';
    }
}
