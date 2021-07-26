<?php

namespace App\Controller\Admin;

use App\Entity\School;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SchoolCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return School::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('School')
            ->setEntityLabelInPlural('School')
            ->setSearchFields(['id', 'name', 'comment', 'zip', 'address', 'city']);
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name');
        $director = AssociationField::new('director');
        $structure = AssociationField::new('structure');
        $address = TextField::new('address');
        $zip = TextField::new('zip');
        $city = TextField::new('city');
        $enable = Field::new('enable');
        $principal = Field::new('principal');
        $comment = TextareaField::new('comment');
        $id = IntegerField::new('id', 'ID');
        $packages = AssociationField::new('packages');
        $author = AssociationField::new('author');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $deletedAt = DateTimeField::new('deletedAt');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $name, $director, $zip, $city, $structure, $principal, $enable, $createdAt];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $name, $director, $address, $zip, $city, $structure, $packages, $principal, $enable, $author, $comment, $createdAt, $updatedAt, $deletedAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$name, $director, $structure, $address, $zip, $city, $enable, $principal, $comment];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$name, $director, $structure, $address, $zip, $city, $enable, $principal, $comment];
        }
    }
}
