<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ClassPeriod;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ClassPeriodCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ClassPeriod::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('ClassPeriod')
            ->setEntityLabelInPlural('ClassPeriod')
            ->setSearchFields(['id', 'comment']);
    }

    public function configureFields(string $pageName): iterable
    {
        $record = DateTimeField::new('record');
        $comment = TextareaField::new('comment');
        $enable = Field::new('enable');
        $classSchool = AssociationField::new('classSchool');
        $period = AssociationField::new('period');
        $students = AssociationField::new('students');
        $teachers = AssociationField::new('teachers');
        $courses = AssociationField::new('courses');
        $author = AssociationField::new('author');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $record, $comment, $enable, $classSchool, $period, $students];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $record, $comment, $enable, $classSchool, $period, $students, $teachers, $courses, $author];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$record, $comment, $enable, $classSchool, $period, $students, $teachers, $courses, $author];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$record, $comment, $enable, $classSchool, $period, $students, $teachers, $courses, $author];
        }
    }
}
