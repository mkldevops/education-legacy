<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Period;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PeriodCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Period::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Period')
            ->setEntityLabelInPlural('Period')
            ->setSearchFields(['id', 'name', 'comment']);
    }

    public function configureFields(string $pageName): iterable
    {
        $begin = DateTimeField::new('begin');
        $end = DateTimeField::new('end');
        $name = TextField::new('name');
        $enable = Field::new('enable');
        $createdAt = DateTimeField::new('createdAt')->hideOnForm();
        $updatedAt = DateTimeField::new('updatedAt')->hideOnForm();
        $deletedAt = DateTimeField::new('deletedAt')->hideOnForm();
        $comment = TextareaField::new('comment');
        $classPeriods = AssociationField::new('classPeriods')->hideOnForm();
        $diploma = AssociationField::new('diploma')->hideOnForm();
        $author = AssociationField::new('author')->hideOnForm();
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $begin, $end, $name, $enable, $createdAt, $deletedAt];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $begin, $end, $name, $enable, $createdAt, $updatedAt, $deletedAt, $comment, $classPeriods, $diploma, $author];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$begin, $end, $name, $enable, $createdAt, $updatedAt, $deletedAt, $comment, $classPeriods, $diploma, $author];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$begin, $end, $name, $enable, $createdAt, $updatedAt, $deletedAt, $comment, $classPeriods, $diploma, $author];
        }
    }
}
