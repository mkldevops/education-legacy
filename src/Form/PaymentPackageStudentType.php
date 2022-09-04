<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\PaymentPackageStudent;
use Fardus\Traits\Symfony\Manager\SessionTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentPackageStudentType extends AbstractType
{
    use SessionTrait;

    public function buildForm(FormBuilderInterface $builder, array $options = []): void
    {
        $builder
            ->add('operation', OperationPaymentStudentType::class, ['label' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PaymentPackageStudent::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_paymentpackagestudent';
    }
}
