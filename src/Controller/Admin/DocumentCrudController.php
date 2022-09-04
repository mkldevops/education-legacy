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
            ->setSearchFields(['id', 'mime', 'path', 'extension', 'size', 'name'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id', 'ID');
        yield TextField::new('mime');
        yield TextField::new('path');
        yield TextField::new('extension');
        yield IntegerField::new('size');
        yield TextField::new('name');
        yield AssociationField::new('school');
        yield AssociationField::new('persons');
        yield AssociationField::new('operations');
        yield AssociationField::new('accountStatements');
        yield AssociationField::new('accountSlips');
        yield AssociationField::new('author');
        yield Field::new('enable');
        yield DateTimeField::new('createdAt')->onlyOnDetail();
        yield DateTimeField::new('updatedAt')->onlyOnDetail();
        yield TextareaField::new('DeletedAt')->onlyOnDetail();
    }
}
