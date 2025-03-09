<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Account;
use App\Exception\AppException;
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

class AccountOFXType extends AbstractType
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $formBuilder
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

        $accountsFieldValidator = static function (FormEvent $formEvent): void {
            $form = $formEvent->getForm();

            try {
                $file = $form->get('file')->getData();
                if (!$file instanceof File) {
                    throw new AppException('Is not file');
                }

                $types = ['application/x-ofx', 'text/plain'];
                if (!\in_array($file->getMimeType(), $types, true)) {
                    throw new AppException('Is not a file of ofx');
                }
            } catch (\Exception $exception) {
                $form->get('file')->addError(new FormError($exception->getMessage()));

                throw new AppException($exception->getMessage(), (int) $exception->getCode(), $exception);
            }
        };

        $formBuilder->addEventListener(FormEvents::POST_SUBMIT, $accountsFieldValidator);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'translation_domain' => 'account',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_account_ofx';
    }
}
