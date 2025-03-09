<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Period;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PeriodCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Period::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Period')
            ->setEntityLabelInPlural('Period')
            ->setSearchFields(['id', 'name', 'comment'])
        ;
    }

    public function configureFields(string $pageName): \Iterator
    {
        yield IntegerField::new('id', 'ID')->hideOnForm();
        yield DateTimeField::new('begin');
        yield DateTimeField::new('end');
        yield TextField::new('name');
        yield TextareaField::new('comment');
        yield AssociationField::new('classPeriods')->hideOnForm();
        yield AssociationField::new('author')->hideOnForm();
        yield Field::new('enable');
        yield DateTimeField::new('createdAt')->hideOnForm();
        yield DateTimeField::new('updatedAt')->hideOnForm();
        yield DateTimeField::new('deletedAt')->hideOnForm();
    }
}
