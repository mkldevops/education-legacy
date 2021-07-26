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
            ->setSearchFields(['id', 'code', 'name']);
    }

    public function configureFields(string $pageName): iterable
    {
        $code = TextField::new('code');
        $name = TextField::new('name');
        $enable = Field::new('enable');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $code, $name, $enable, $createdAt];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $code, $name, $enable, $createdAt, $updatedAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$code, $name, $enable, $createdAt, $updatedAt];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$code, $name, $enable, $createdAt, $updatedAt];
        }
    }
}
