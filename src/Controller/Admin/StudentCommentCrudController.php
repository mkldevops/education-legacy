<?php

namespace App\Controller\Admin;

use App\Entity\StudentComment;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class StudentCommentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return StudentComment::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('StudentComment')
            ->setEntityLabelInPlural('StudentComment')
            ->setSearchFields(['id', 'title', 'text', 'type']);
    }

    public function configureFields(string $pageName): iterable
    {
        $title = TextField::new('title');
        $text = TextareaField::new('text');
        $type = TextField::new('type');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $enable = Field::new('enable');
        $student = AssociationField::new('student');
        $author = AssociationField::new('author');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $title, $type, $createdAt, $enable, $student, $author];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $title, $text, $type, $createdAt, $updatedAt, $enable, $student, $author];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$title, $text, $type, $createdAt, $updatedAt, $enable, $student, $author];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$title, $text, $type, $createdAt, $updatedAt, $enable, $student, $author];
        }
    }
}
