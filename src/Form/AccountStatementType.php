<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\AccountStatement;
use App\Form\Type\DatePickerType;
use App\Form\Type\MonthPickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AccountStatementType.
 */
class AccountStatementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options = []): void
    {
        $builder
            ->add('title')
            ->add('month', MonthPickerType::class)
            ->add('begin', DatePickerType::class)
            ->add('end', DatePickerType::class)
            ->add('amountCredit')
            ->add('amountDebit')
            ->add('newBalance')
            ->add('numberOperations')
            ->add('reference')
            ->add('enable')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AccountStatement::class,
            'translation_domain' => 'account_statement',
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'app_accountstatement';
    }
}
