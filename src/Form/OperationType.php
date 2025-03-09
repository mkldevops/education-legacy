<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Account;
use App\Entity\Operation;
use App\Entity\OperationGender;
use App\Entity\School;
use App\Entity\TypeOperation;
use App\Entity\User;
use App\Exception\AppException;
use App\Form\Type\CkeditorType;
use App\Model\SchoolList;
use App\Repository\AccountRepository;
use App\Repository\OperationGenderRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OperationType extends AbstractType
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly Security $security
    ) {}

    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $formBuilder
            ->add('account', EntityType::class, [
                'label' => 'form.account',
                'class' => Account::class,
                'choice_label' => 'name',
                'query_builder' => function (AccountRepository $accountRepository): QueryBuilder {
                    /** @var SchoolList $schoolList */
                    $schoolList = $this->requestStack->getSession()->get('school');
                    if (!$schoolList->school instanceof School) {
                        throw new AppException('School selected not set');
                    }

                    return $accountRepository->getAccountsQB($schoolList->school, false);
                },
            ])
            ->add('name', TextType::class, [
                'label' => 'form.name',
                'required' => false,
            ])
            ->add('date', DateType::class, [
                'label' => 'form.date',
                'widget' => 'single_text',
                'data' => new \DateTime(),
            ])
            ->add('operationGender', EntityType::class, [
                'label' => 'form.operation_gender',
                'class' => OperationGender::class,
                'choice_label' => 'name',
                'query_builder' => static fn (OperationGenderRepository $operationGenderRepository): QueryBuilder => $operationGenderRepository->getAvailable(),
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
                'preferred_choices' => [$this->security->getUser()],
                'query_builder' => static fn (UserRepository $userRepository): QueryBuilder => $userRepository->getAvailable(),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'data_class' => Operation::class,
            'translation_domain' => 'operation',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_operation';
    }
}
