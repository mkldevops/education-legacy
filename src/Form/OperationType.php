<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Account;
use App\Entity\Operation;
use App\Entity\OperationGender;
use App\Entity\TypeOperation;
use App\Entity\User;
use App\Exception\InvalidArgumentException;
use App\Form\Type\CkeditorType;
use App\Repository\AccountRepository;
use App\Repository\OperationGenderRepository;
use App\Repository\UserRepository;
use Fardus\Traits\Symfony\Manager\SessionTrait;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class OperationType.
 */
class OperationType extends AbstractType
{
    use SessionTrait;

    protected User $user;

    /**
     * @required
     *
     * @throws InvalidArgumentException
     */
    public function setUser(TokenStorageInterface $tokenStorage): self
    {
        $user = $tokenStorage->getToken()?->getUser();
        if (!$user instanceof User) {
            throw new InvalidArgumentException('The user of token storage is not instance of UserInterface');
        }
        $this->user = $user;

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('account', EntityType::class, [
                'label' => 'form.account',
                'class' => Account::class,
                'choice_label' => 'name',
                'query_builder' => function (AccountRepository $er) {
                    $school = $this->getSession()->get('school');

                    return $er->getAccountsQB($school, false);
                },
            ])
            ->add('name', TextType::class, [
                'label' => 'form.name',
                'required' => false,
            ])
            ->add('date', DateType::class, [
                'label' => 'form.date',
                'widget' => 'single_text',
                'empty_data' => new \DateTime(),
            ])
            ->add('operationGender', EntityType::class, [
                'label' => 'form.operation_gender',
                'class' => OperationGender::class,
                'choice_label' => 'name',
                'query_builder' => fn (OperationGenderRepository $er) => $er->getAvailable(),
            ])
            ->add('typeOperation', EntityType::class, [
                'label' => 'form.type_operation',
                'class' => TypeOperation::class,
                'choice_label' => 'name',
            ])
            ->add('amount', MoneyType::class, ['label' => 'form.amount'])
            ->add('reference', TextType::class, [
                'label' => 'form.reference',
                'required' => false,
            ])
            ->add('comment', CkeditorType::class, [
                'label' => 'form.comment',
                'required' => false,
            ])
            ->add('author', EntityType::class, [
                'label' => 'form.author',
                'class' => User::class,
                'choice_label' => 'nameComplete',
                'preferred_choices' => [$this->user],
                'query_builder' => fn (UserRepository $er) => $er->getAvailable(),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Operation::class,
            'translation_domain' => 'operation',
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'app_operation';
    }
}
