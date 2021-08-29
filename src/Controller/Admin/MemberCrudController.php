<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Member;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MemberCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Member::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Member')
            ->setEntityLabelInPlural('Member')
            ->setSearchFields(['id', 'positionName', 'name']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id', 'ID');
        yield TextField::new('name');
        yield TextField::new('positionName');
        yield AssociationField::new('person');
        yield AssociationField::new('structure');
        yield AssociationField::new('author');
        yield BooleanField::new('enable');
        yield DateTimeField::new('createdAt');
        yield DateTimeField::new('updatedAt');
        yield DateTimeField::new('deletedAt');
    }
}
