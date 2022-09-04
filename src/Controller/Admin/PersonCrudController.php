<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Person;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PersonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Person::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Person')
            ->setEntityLabelInPlural('Person')
            ->setSearchFields(['id', 'forname', 'phone', 'email', 'birthplace', 'gender', 'address', 'zip', 'city', 'name'])
        ;
    }

    public function configureFields(string $pageName = null): iterable
    {
        yield IntegerField::new('id', 'ID');
        yield TextField::new('forname');
        yield TextField::new('phone');
        yield TextField::new('email')->hideOnIndex();
        yield DateTimeField::new('birthday')->hideOnIndex();
        yield TextField::new('birthplace')->hideOnIndex();
        yield TextField::new('gender');
        yield TextField::new('address');
        yield TextField::new('zip');
        yield TextField::new('city');
        yield TextField::new('name');
        yield Field::new('enable');
        yield DateTimeField::new('createdAt')->onlyOnDetail();
        yield DateTimeField::new('updatedAt')->onlyOnDetail();
        yield DateTimeField::new('deletedAt')->onlyOnDetail();
        yield AssociationField::new('user');
        yield AssociationField::new('member');
        yield AssociationField::new('student');
        yield AssociationField::new('schools');
        yield AssociationField::new('image')->hideOnIndex();
        yield AssociationField::new('family');
        yield AssociationField::new('author')->hideOnIndex();
    }
}
