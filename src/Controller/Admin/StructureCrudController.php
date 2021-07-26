<?php

namespace App\Controller\Admin;

use App\Entity\Structure;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class StructureCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Structure::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Structure')
            ->setEntityLabelInPlural('Structure')
            ->setSearchFields(['id', 'logo', 'options', 'name', 'address', 'city', 'zip']);
    }

    public function configureFields(string $pageName): iterable
    {
        $logo = TextField::new('logo');
        $name = TextField::new('name');
        $enable = Field::new('enable');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $deletedAt = DateTimeField::new('deletedAt');
        $address = TextField::new('address');
        $city = TextField::new('city');
        $zip = TextField::new('zip');
        $president = AssociationField::new('president');
        $treasurer = AssociationField::new('treasurer');
        $secretary = AssociationField::new('secretary');
        $members = AssociationField::new('members');
        $accounts = AssociationField::new('accounts');
        $accountSlips = AssociationField::new('accountSlips');
        $author = AssociationField::new('author');
        $id = IntegerField::new('id', 'ID');
        $options = TextField::new('options');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $logo, $name, $enable, $createdAt, $deletedAt, $address];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $logo, $options, $name, $enable, $createdAt, $updatedAt, $deletedAt, $address, $city, $zip, $president, $treasurer, $secretary, $members, $accounts, $accountSlips, $author];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$logo, $name, $enable, $createdAt, $updatedAt, $deletedAt, $address, $city, $zip, $president, $treasurer, $secretary, $members, $accounts, $accountSlips, $author];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$logo, $name, $enable, $createdAt, $updatedAt, $deletedAt, $address, $city, $zip, $president, $treasurer, $secretary, $members, $accounts, $accountSlips, $author];
        }
    }
}
