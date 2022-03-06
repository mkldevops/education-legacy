<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ClassSchool;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Core\Security;

class ClassSchoolCrudController extends AbstractCrudController
{
    public function __construct(private Security $security)
    {
    }

    public static function getEntityFqcn(): string
    {
        return ClassSchool::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('ClassSchool')
            ->setEntityLabelInPlural('ClassSchool')
            ->setSearchFields(['id', 'ageMinimum', 'ageMaximum', 'name', 'description']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id', 'ID')->hideOnForm();
        yield TextField::new('name');
        yield TextareaField::new('description');
        yield IntegerField::new('ageMinimum');
        yield IntegerField::new('ageMaximum');
        yield AssociationField::new('school');
        yield AssociationField::new('classPeriods');
        yield BooleanField::new('enable');
        yield AssociationField::new('author')->setValue($this->security->getUser());
        yield DateTimeField::new('createdAt')->onlyOnDetail();
        yield DateTimeField::new('updatedAt')->onlyOnDetail();
        yield DateTimeField::new('deletedAt')->onlyOnDetail();
    }
}
