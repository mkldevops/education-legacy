<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\AccountSlip;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AccountSlipeditType.
 */
class AccountSlipEditType extends AccountSlipType
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        parent::buildForm($formBuilder, $options);

        $formBuilder->addEventListener(FormEvents::POST_SET_DATA, static function (FormEvent $formEvent): void {
            /** @var AccountSlip $accountSlip */
            $accountSlip = $formEvent->getData();
            $form = $formEvent->getForm();
            if ($accountSlip->hasOperationDebit()) {
                $form->remove('accountDebit');
            }

            if ($accountSlip->hasOperationCredit()) {
                $form->remove('accountCredit');
            }
        });
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
        return 'app_accountslip_edit';
    }
}
