<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ClassSchool;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ClassSchoolCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ClassSchool::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('ClassSchool')
            ->setEntityLabelInPlural('ClassSchool')
            ->setSearchFields(['id', 'ageMinimum', 'ageMaximum', 'name', 'description']);
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name');
        $description = TextareaField::new('description');
        $ageMinimum = IntegerField::new('ageMinimum');
        $ageMaximum = IntegerField::new('ageMaximum');
        $school = AssociationField::new('school');
        $enable = Field::new('enable');
        $id = IntegerField::new('id', 'ID');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $deletedAt = DateTimeField::new('deletedAt');
        $classPeriods = AssociationField::new('classPeriods');
        $author = AssociationField::new('author');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $ageMinimum, $ageMaximum, $name, $enable, $createdAt, $deletedAt];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $ageMinimum, $ageMaximum, $name, $enable, $createdAt, $updatedAt, $deletedAt, $description, $classPeriods, $author, $school];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$name, $description, $ageMinimum, $ageMaximum, $school, $enable];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$name, $description, $ageMinimum, $ageMaximum, $school, $enable];
        }
    }
}
