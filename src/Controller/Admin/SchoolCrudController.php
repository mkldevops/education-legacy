<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\School;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SchoolCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return School::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('School')
            ->setEntityLabelInPlural('School')
            ->setSearchFields(['id', 'name', 'comment', 'zip', 'address', 'city']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id', 'ID');
        yield TextField::new('name');
        yield AssociationField::new('director');
        yield AssociationField::new('structure');
        yield TextField::new('address');
        yield TextField::new('zip');
        yield TextField::new('city');
        yield Field::new('principal');
        yield TextareaField::new('comment');
        yield AssociationField::new('packages');
        yield Field::new('enable');
        yield AssociationField::new('author');
        yield DateTimeField::new('createdAt')->onlyOnDetail();
        yield DateTimeField::new('updatedAt')->onlyOnDetail();
        yield DateTimeField::new('deletedAt')->onlyOnDetail();
    }
}
