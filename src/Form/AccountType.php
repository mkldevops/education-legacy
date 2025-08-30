<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Account;
use App\Entity\School;
use App\Entity\Structure;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AccountType.
 */
class AccountType extends AbstractType
{
    /**
     * buildForm.
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $formBuilder
            ->add('name')
            ->add('structure', EntityType::class, [
                'label' => 'form.label.structure',
                'class' => Structure::class,
                'choice_label' => 'name',
            ])
            ->add('school', EntityType::class, [
                'label' => 'form.label.school',
                'class' => School::class,
                'choice_label' => 'name',
            ])
            ->add('principal', CheckboxType::class, [
                'required' => false,
                'label' => 'form.label.principal',
            ])
            ->add('enableAccountStatement', CheckboxType::class, [
                'required' => false,
                'label' => 'form.label.enableAccountStatement',
            ])
            ->add('intervalOperationsAccountStatement', null, [
                'label' => 'form.label.intervalOperationsAccountStatement',
            ])
            ->add('isBank', CheckboxType::class, [
                'required' => false,
                'label' => 'form.label.isBank',
            ])
            ->add('bankName', null, [
                'label' => 'form.label.bankName',
            ])
            ->add('bankAddress', null, [
                'label' => 'form.label.bankAddress',
            ])
            ->add('bankIban', null, [
                'label' => 'form.label.bankIban',
            ])
            ->add('bankBic', null, [
                'label' => 'form.label.bankBic',
            ])
            ->add('enable', CheckboxType::class, [
                'required' => false,
                'label' => 'form.label.status',
            ])
        ;
    }

    /**
     * configureOptions.
     */
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'data_class' => Account::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention' => 'task_item',
            'translation_domain' => 'account',
        ]);
    }

    /**
     * getName.
     */
    public function getBlockPrefix(): string
    {
        return 'app_account';
    }
}
