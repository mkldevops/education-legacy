<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class OperationPaymentStudentType.
 */
class OperationPaymentStudentType extends OperationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->remove('name')
                ->remove('typeOperation')
                ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'app_operationpaymentstudent';
    }
}
