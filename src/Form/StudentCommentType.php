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

/**
 * Class StudentCommentType.
 */
class StudentCommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\StudentComment',
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'app_studentcomment';
    }
}
