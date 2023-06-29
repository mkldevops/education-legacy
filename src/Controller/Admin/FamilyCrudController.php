<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Family;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class FamilyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Family::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Family')
            ->setEntityLabelInPlural('Family')
            ->setSearchFields(['id', 'language', 'numberChildren', 'address', 'city', 'personAuthorized', 'personEmergency', 'name', 'email', 'zip'])
        ;
    }

    public function configureFields(string $pageName): \Iterator
    {
        yield IntegerField::new('id', 'ID');
        yield TextField::new('language');
        yield IntegerField::new('numberChildren');
        yield TextareaField::new('address')->hideOnIndex();
        yield TextField::new('city');
        yield TextField::new('personAuthorized')->hideOnIndex();
        yield TextField::new('personEmergency')->hideOnIndex();
        yield TextField::new('name');
        yield Field::new('enable');
        yield DateTimeField::new('createdAt')->onlyOnDetail();
        yield DateTimeField::new('updatedAt')->onlyOnDetail();
        yield DateTimeField::new('deletedAt')->onlyOnDetail();
        yield TextField::new('email');
        yield TextField::new('zip');
        yield AssociationField::new('father');
        yield AssociationField::new('mother');
        yield AssociationField::new('legalGuardian');
        yield AssociationField::new('persons');
        yield AssociationField::new('author');
    }
}
