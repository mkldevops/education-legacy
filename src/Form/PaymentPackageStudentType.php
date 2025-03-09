<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\PaymentPackageStudent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentPackageStudentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options = []): void
    {
        $formBuilder
            ->add('operation', OperationPaymentStudentType::class, ['label' => false])
        ;
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'data_class' => PaymentPackageStudent::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_paymentpackagestudent';
    }
}
