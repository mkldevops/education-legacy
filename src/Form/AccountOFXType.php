<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Account;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File as FileConstraints;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class AccountOFXType.
 */
class AccountOFXType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'mapped' => false,
                'label' => 'account.ofx.form.file',
                'constraints' => [
                    new NotBlank(),
                    new FileConstraints([
                        'mimeTypes' => ['application/x-ofx', 'text/plain'],
                    ]),
                ],
            ])
            ->add('accountTransfer', EntityType::class, [
                'class' => Account::class,
                'label' => 'account.ofx.form.account_transfer',
                'mapped' => false,
            ])
        ;

        /**
         * @param \Symfony\Component\Form\FormEvent $event
         */
        $accountsFieldValidator = function (FormEvent $event): void {
            $form = $event->getForm();

            try {
                $file = $form->get('file')->getData();
                if (!$file instanceof File) {
                    throw new \Exception('Is not file');
                }

                $types = ['application/x-ofx', 'text/plain'];
                if (!in_array($file->getMimeType(), $types, true)) {
                    throw new \Exception('Is not a file of ofx');
                }
            } catch (\Exception $e) {
                $form->get('file')->addError(new FormError($e->getMessage()));
            }
        };

        $builder->addEventListener(FormEvents::POST_SUBMIT, $accountsFieldValidator);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'account',
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'app_account_ofx';
    }
}
