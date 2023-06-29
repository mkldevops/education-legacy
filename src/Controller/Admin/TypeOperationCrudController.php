<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\TypeOperation;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TypeOperationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TypeOperation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('TypeOperation')
            ->setEntityLabelInPlural('TypeOperation')
            ->setSearchFields(['id', 'shortName', 'code', 'typeAmount', 'description', 'name'])
        ;
    }

    public function configureFields(string $pageName): \Iterator
    {
        yield IntegerField::new('id', 'ID');
        yield TextField::new('shortName');
        yield TextField::new('code');
        yield TextField::new('typeAmount');
        yield TextareaField::new('description');
        yield Field::new('isInternalTransfert');
        yield TextField::new('name');
        yield Field::new('enable');
        yield AssociationField::new('parent');
        yield AssociationField::new('typeOperations');
        yield AssociationField::new('author');
        yield DateTimeField::new('createdAt')->onlyOnDetail();
        yield DateTimeField::new('updatedAt')->onlyOnDetail();
        yield DateTimeField::new('deletedAt')->onlyOnDetail();
    }
}
