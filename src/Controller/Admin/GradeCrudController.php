<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Grade;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class GradeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Grade::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Grade')
            ->setEntityLabelInPlural('Grade')
            ->setSearchFields(['id', 'name', 'description'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id', 'ID');
        yield TextField::new('name');
        yield Field::new('enable');
        yield TextareaField::new('description');
        yield DateTimeField::new('createdAt');
        yield DateTimeField::new('updatedAt');
    }
}
