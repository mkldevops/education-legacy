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
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event): void {
            /** @var AccountSlip $accountSlip */
            $accountSlip = $event->getData();
            $form = $event->getForm();

            if ($accountSlip->hasOperationDebit()) {
                $form->remove('accountDebit');
            }

            if ($accountSlip->hasOperationCredit()) {
                $form->remove('accountCredit');
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AccountSlip::class,
            'translation_domain' => 'account_slip',
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'app_accountslip_edit';
    }
}
