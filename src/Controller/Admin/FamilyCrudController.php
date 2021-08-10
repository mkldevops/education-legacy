<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Family;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class FamilyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Family::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Family')
            ->setEntityLabelInPlural('Family')
            ->setSearchFields(['id', 'language', 'numberChildren', 'address', 'city', 'personAuthorized', 'personEmergency', 'name', 'email', 'zip']);
    }

    public function configureFields(string $pageName): iterable
    {
        $language = TextField::new('language');
        $numberChildren = IntegerField::new('numberChildren');
        $address = TextareaField::new('address');
        $city = TextField::new('city');
        $personAuthorized = TextField::new('personAuthorized');
        $personEmergency = TextField::new('personEmergency');
        $name = TextField::new('name');
        $enable = Field::new('enable');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $deletedAt = DateTimeField::new('deletedAt');
        $email = TextField::new('email');
        $zip = TextField::new('zip');
        $father = AssociationField::new('father');
        $mother = AssociationField::new('mother');
        $legalGuardian = AssociationField::new('legalGuardian');
        $persons = AssociationField::new('persons');
        $author = AssociationField::new('author');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            $nameComplete = TextareaField::new('nameComplete');

            return [$id, $nameComplete, $persons, $numberChildren, $zip, $city, $enable, $createdAt, $author];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $language, $numberChildren, $address, $city, $personAuthorized, $personEmergency, $name, $enable, $createdAt, $updatedAt, $deletedAt, $email, $zip, $father, $mother, $legalGuardian, $persons, $author];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$language, $numberChildren, $address, $city, $personAuthorized, $personEmergency, $name, $enable, $createdAt, $updatedAt, $deletedAt, $email, $zip, $father, $mother, $legalGuardian, $persons, $author];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$language, $numberChildren, $address, $city, $personAuthorized, $personEmergency, $name, $enable, $createdAt, $updatedAt, $deletedAt, $email, $zip, $father, $mother, $legalGuardian, $persons, $author];
        }
    }
}
