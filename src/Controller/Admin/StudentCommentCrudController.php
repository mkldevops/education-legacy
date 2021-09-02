<?php

declare(strict_types=1);

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
        yield TextField::new('title');
        yield TextareaField::new('text');
        yield TextField::new('type');
        yield DateTimeField::new('createdAt')->onlyOnDetail();
        yield DateTimeField::new('updatedAt')->onlyOnDetail();
        yield Field::new('enable');
        yield AssociationField::new('student');
        yield AssociationField::new('author');
        yield IntegerField::new('id', 'ID');
    }
}
