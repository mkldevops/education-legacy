<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\TypeOperation;
use App\Repository\TypeOperationRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TypeOperationType.
 */
class TypeOperationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $typeAmounts = [
            TypeOperation::TYPE_AMOUNT_NEGATIVE => 'Négatif',
            TypeOperation::TYPE_AMOUNT_POSITIVE => 'Positif',
            TypeOperation::TYPE_AMOUNT_MIXTE => 'Mixte',
        ];

        $formBuilder
            ->add('name')
            ->add('shortName')
            ->add('code')
            ->add('typeAmount', ChoiceType::class, [
                'label' => 'Type de montant',
                'placeholder' => 'Aucun',
                'choices' => $typeAmounts,
            ])
            ->add('parent', EntityType::class, [
                'required' => false,
                'placeholder' => 'Aucun',
                'label' => "Type d'opération parent",
                'class' => TypeOperation::class,
                'choice_label' => 'name',
                'query_builder' => static fn (TypeOperationRepository $typeOperationRepository): QueryBuilder => $typeOperationRepository->getParents(),
            ])
            ->add('description')
            ->add('isInternalTransfert')
            ->add('status')
        ;
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'data_class' => TypeOperation::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_type_operation';
    }
}
