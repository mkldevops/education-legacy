<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\TypeOperation;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TypeOperationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TypeOperation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('TypeOperation')
            ->setEntityLabelInPlural('TypeOperation')
            ->setSearchFields(['id', 'shortName', 'code', 'typeAmount', 'description', 'name']);
    }

    public function configureFields(string $pageName): iterable
    {
        $shortName = TextField::new('shortName');
        $code = TextField::new('code');
        $typeAmount = TextField::new('typeAmount');
        $description = TextareaField::new('description');
        $isInternalTransfert = Field::new('isInternalTransfert');
        $status = Field::new('status');
        $name = TextField::new('name');
        $enable = Field::new('enable');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $deletedAt = DateTimeField::new('deletedAt');
        $parent = AssociationField::new('parent');
        $typeOperations = AssociationField::new('typeOperations');
        $author = AssociationField::new('author');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $shortName, $code, $typeAmount, $isInternalTransfert, $status, $name];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $shortName, $code, $typeAmount, $description, $isInternalTransfert, $status, $name, $enable, $createdAt, $updatedAt, $deletedAt, $parent, $typeOperations, $author];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$shortName, $code, $typeAmount, $description, $isInternalTransfert, $status, $name, $enable, $createdAt, $updatedAt, $deletedAt, $parent, $typeOperations, $author];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$shortName, $code, $typeAmount, $description, $isInternalTransfert, $status, $name, $enable, $createdAt, $updatedAt, $deletedAt, $parent, $typeOperations, $author];
        }
    }
}
