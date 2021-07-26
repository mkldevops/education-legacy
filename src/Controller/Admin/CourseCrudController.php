<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Course;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;

class CourseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Course::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Course')
            ->setEntityLabelInPlural('Course')
            ->setSearchFields(['id', 'idEvent', 'text', 'comment']);
    }

    public function configureFields(string $pageName): iterable
    {
        $idEvent = TextField::new('idEvent');
        $date = DateField::new('date');
        $text = TextareaField::new('text');
        $hourBegin = TimeField::new('hourBegin');
        $hourEnd = TimeField::new('hourEnd');
        $comment = TextField::new('comment');
        $enable = Field::new('enable');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $students = AssociationField::new('students');
        $classPeriod = AssociationField::new('classPeriod');
        $teachers = AssociationField::new('teachers');
        $author = AssociationField::new('author');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $classPeriod, $date, $enable, $createdAt];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $idEvent, $date, $text, $hourBegin, $hourEnd, $comment, $enable, $createdAt, $updatedAt, $students, $classPeriod, $teachers, $author];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$idEvent, $date, $text, $hourBegin, $hourEnd, $comment, $enable, $createdAt, $updatedAt, $students, $classPeriod, $teachers, $author];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$idEvent, $date, $text, $hourBegin, $hourEnd, $comment, $enable, $createdAt, $updatedAt, $students, $classPeriod, $teachers, $author];
        }
    }
}
