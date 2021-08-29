<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Structure;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class StructureCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Structure::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Structure')
            ->setEntityLabelInPlural('Structure')
            ->setSearchFields(['id', 'logo', 'options', 'name', 'address', 'city', 'zip']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id', 'ID');
        yield TextField::new('logo');
        yield TextField::new('name');
        yield TextField::new('address');
        yield TextField::new('city');
        yield TextField::new('zip');
        yield AssociationField::new('president');
        yield AssociationField::new('treasurer');
        yield AssociationField::new('secretary');
        yield AssociationField::new('members');
        yield AssociationField::new('accounts');
        yield AssociationField::new('accountSlips');
        yield AssociationField::new('author');
        yield ArrayField::new('options');
        yield Field::new('enable');
        yield DateTimeField::new('createdAt')->onlyOnDetail();
        yield DateTimeField::new('updatedAt')->onlyOnDetail();
        yield DateTimeField::new('deletedAt')->onlyOnDetail();
    }
}
