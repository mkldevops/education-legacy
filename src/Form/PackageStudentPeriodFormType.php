<?php

declare(strict_types=1);

namespace App\Form;

use App\Dto\PackageStudentPeriodCreateDto;
use App\Entity\Package;
use App\Entity\School;
use App\Repository\PackageRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Service\Attribute\Required;

class PackageStudentPeriodFormType extends AbstractType
{
    protected School $school;

    #[Required]
    public function setSchoolBySession(RequestStack $requestStack): void
    {
        $this->school = $requestStack->getSession()->get('school')->school;
    }

    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $formBuilder
            ->add('packageId', EntityType::class, [
                'class' => Package::class,
                'choice_label' => 'NameWithPrice',
                'choice_value' => 'id',
                'query_builder' => fn (PackageRepository $packageRepository): QueryBuilder => $packageRepository->getAvailable($this->school),
                'placeholder' => 'Select a package...',
            ])
            ->add('discount', MoneyType::class, [
                'required' => false,
                'empty_data' => '0',
            ])
            ->add('comment', TextareaType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'data_class' => PackageStudentPeriodCreateDto::class,
        ]);
    }
}
