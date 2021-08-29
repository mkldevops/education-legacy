<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Course;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;

class CourseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Course::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Course')
            ->setEntityLabelInPlural('Course')
            ->setSearchFields(['id', 'idEvent', 'text', 'comment']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id', 'ID');
        yield TextField::new('idEvent');
        yield DateField::new('date');
        yield TextareaField::new('text');
        yield TimeField::new('hourBegin');
        yield TimeField::new('hourEnd');
        yield TextField::new('comment');
        yield AssociationField::new('students');
        yield AssociationField::new('classPeriod');
        yield AssociationField::new('teachers');
        yield AssociationField::new('author')->hideOnForm();
        yield Field::new('enable');
        yield DateTimeField::new('createdAt')->onlyOnDetail();
        yield DateTimeField::new('updatedAt')->onlyOnDetail();
    }
}
