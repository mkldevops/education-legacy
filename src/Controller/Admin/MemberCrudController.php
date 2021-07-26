<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Member;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
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
        $name = TextField::new('name');
        $positionName = TextField::new('positionName');
        $person = AssociationField::new('person');
        $structure = AssociationField::new('structure');
        $enable = Field::new('enable');
        $id = IntegerField::new('id', 'ID');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $deletedAt = DateTimeField::new('deletedAt');
        $author = AssociationField::new('author');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $name, $positionName, $person, $structure, $enable, $createdAt, $author];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $positionName, $name, $enable, $createdAt, $updatedAt, $deletedAt, $person, $structure, $author];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$name, $positionName, $person, $structure, $enable];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$name, $positionName, $person, $structure, $enable];
        }
    }
}
