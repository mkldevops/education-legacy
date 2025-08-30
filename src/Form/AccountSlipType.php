<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Account;
use App\Entity\AccountSlip;
use App\Form\Type\CkeditorType;
use App\Form\Type\DatePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AccountSlipType.
 */
class AccountSlipType extends AbstractType
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $formBuilder
            ->add('gender', ChoiceType::class, [
                'choices' => AccountSlip::getGenders(),
            ])
            ->add('accountCredit', EntityType::class, [
                'class' => Account::class,
                'mapped' => false,
            ])
            ->add('accountDebit', EntityType::class, [
                'class' => Account::class,
                'mapped' => false,
            ])
            ->add('date', DatePickerType::class)
            ->add('amount')
            ->add('uniqueId', null, [
                'required' => false,
            ])
            ->add('reference')
            ->add('comment', CkeditorType::class, [
                'required' => false,
            ])
        ;

        /**
         * @param FormEvent $event
         */
        $accountsFieldValidator = static function (FormEvent $formEvent): void {
            $form = $formEvent->getForm();
            if ($form->has('accountCredit')) {
                $accountCredit = $form->get('accountCredit')->getData();
                $accountDebit = $form->get('accountDebit')->getData();

                if ($accountCredit === $accountDebit) {
                    $form['accountCredit']
                        ->addError(new FormError('accountCredit and accountDebit may not be the same'))
                    ;
                }
            }
        };

        // adding the validator to the FormBuilderInterface
        $formBuilder->addEventListener(FormEvents::POST_SUBMIT, $accountsFieldValidator);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'data_class' => AccountSlip::class,
            'translation_domain' => 'account_slip',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_accountslip';
    }
}
