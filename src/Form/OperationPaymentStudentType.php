<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\FormBuilderInterface;

class OperationPaymentStudentType extends OperationType
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        parent::buildForm($formBuilder, $options);

        $formBuilder->remove('name')
            ->remove('typeOperation')
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_operationpaymentstudent';
    }
}
