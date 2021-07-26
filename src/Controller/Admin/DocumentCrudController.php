<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Document;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DocumentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Document::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Document')
            ->setEntityLabelInPlural('Document')
            ->setSearchFields(['id', 'mime', 'path', 'extension', 'size', 'name']);
    }

    public function configureFields(string $pageName): iterable
    {
        $mime = TextField::new('mime');
        $path = TextField::new('path');
        $extension = TextField::new('extension');
        $size = IntegerField::new('size');
        $name = TextField::new('name');
        $enable = Field::new('enable');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $school = AssociationField::new('school');
        $persons = AssociationField::new('persons');
        $operations = AssociationField::new('operations');
        $accountStatements = AssociationField::new('accountStatements');
        $accountSlips = AssociationField::new('accountSlips');
        $author = AssociationField::new('author');
        $id = IntegerField::new('id', 'ID');
        $deletedAt = TextareaField::new('DeletedAt');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $mime, $path, $extension, $size, $name, $enable];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $path, $mime, $extension, $size, $name, $school, $createdAt, $updatedAt, $deletedAt, $enable];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$mime, $path, $extension, $size, $name, $enable, $createdAt, $updatedAt, $deletedAt, $school, $persons, $operations, $accountStatements, $accountSlips, $author];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$mime, $path, $extension, $size, $name, $enable, $createdAt, $updatedAt, $deletedAt, $school, $persons, $operations, $accountStatements, $accountSlips, $author];
        }
    }
}
