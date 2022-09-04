<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\OperationGender;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class OperationGenderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return OperationGender::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('OperationGender')
            ->setEntityLabelInPlural('OperationGender')
            ->setSearchFields(['id', 'code', 'name'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id', 'ID');
        yield TextField::new('code');
        yield TextField::new('name');
        yield Field::new('enable');
        yield DateTimeField::new('createdAt');
        yield DateTimeField::new('updatedAt');
    }
}
