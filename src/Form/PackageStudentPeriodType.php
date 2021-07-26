<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Package;
use App\Entity\PackageStudentPeriod;
use App\Entity\School;
use App\Repository\PackageRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PackageStudentPeriodType.
 */
class PackageStudentPeriodType extends AbstractType
{
    /**
     * @var School
     */
    protected ?School $school = null;

    /**
     * @required
     */
    public function setSchoolBySession(SessionInterface $session): void
    {
        $this->school = $session->get('school')->selected;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('package', EntityType::class, [
                'class' => Package::class,
                'choice_label' => 'NameWithPrice',
                'query_builder' => fn (PackageRepository $er) => $er->getAvailable($this->school),
            ])
            ->add('student')
            ->add('discount', MoneyType::class)
            ->add('comment', TextareaType::class, ['required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PackageStudentPeriod::class,
            'translation_domain' => 'package_period_student',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_package_student_period';
    }
}
