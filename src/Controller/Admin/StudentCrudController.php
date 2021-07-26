<?php

namespace App\Controller\Admin;

use App\Entity\Student;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class StudentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Student::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Student')
            ->setEntityLabelInPlural('Student')
            ->setSearchFields(['id', 'lastSchool', 'personAuthorized', 'remarksHealth']);
    }

    public function configureFields(string $pageName): iterable
    {
        $personName = Field::new('person.name');
        $personForname = Field::new('person.forname');
        $grade = AssociationField::new('grade');
        $lastSchool = TextField::new('lastSchool');
        $letAlone = Field::new('letAlone');
        $enable = Field::new('enable');
        $school = AssociationField::new('school');
        $id = IntegerField::new('id', 'ID');
        $personAuthorized = TextField::new('personAuthorized');
        $remarksHealth = TextareaField::new('remarksHealth');
        $dateRegistration = DateTimeField::new('dateRegistration');
        $dateDesactivated = DateTimeField::new('dateDesactivated');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $deletedAt = DateTimeField::new('deletedAt');
        $person = AssociationField::new('person');
        $classPeriods = AssociationField::new('classPeriods');
        $packagePeriods = AssociationField::new('packagePeriods');
        $courses = AssociationField::new('courses');
        $comments = AssociationField::new('comments');
        $author = AssociationField::new('author');

        if (Crud::PAGE_INDEX === $pageName) {
            $age = TextareaField::new('age');
            return [$id, $person, $age, $letAlone, $grade, $school, $enable, $createdAt];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $lastSchool, $personAuthorized, $remarksHealth, $letAlone, $dateRegistration, $dateDesactivated, $enable, $createdAt, $updatedAt, $deletedAt, $person, $grade, $classPeriods, $packagePeriods, $courses, $comments, $author, $school];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$personName, $personForname, $grade, $lastSchool, $letAlone, $enable, $school];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$personName, $personForname, $grade, $lastSchool, $letAlone, $enable, $school];
        }
    }
}
