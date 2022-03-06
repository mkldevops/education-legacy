<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ClassPeriod;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class ClassPeriodCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ClassPeriod::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('ClassPeriod')
            ->setEntityLabelInPlural('ClassPeriod')
            ->setSearchFields(['id', 'comment']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id', 'ID')->hideOnForm();
        yield AssociationField::new('classSchool');
        yield AssociationField::new('period');
        yield AssociationField::new('students');
        yield AssociationField::new('teachers');
        yield AssociationField::new('courses');
        yield TextareaField::new('comment')->hideOnIndex();
        yield Field::new('enable');
        yield AssociationField::new('author');
        yield DateTimeField::new('createdAt')->onlyOnDetail();
        yield DateTimeField::new('updatedAt')->onlyOnDetail();
    }
}
