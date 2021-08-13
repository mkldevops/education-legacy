<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User')
            ->setEntityLabelInPlural('User')
            ->setSearchFields(['id', 'username', 'roles', 'surname', 'email', 'name']);
    }

    public function configureFields(string $pageName): iterable
    {
        $username = TextField::new('username');
        $plainPassword = Field::new('plainPassword');
        $name = TextField::new('name');
        $surname = TextField::new('surname');
        $email = TextField::new('email');
        $roles = TextField::new('roles');
        $schoolAccessRight = AssociationField::new('schoolAccessRight');
        $enable = Field::new('enable');
        $id = IntegerField::new('id', 'ID');
        $password = TextField::new('password');
        $lastLogin = DateTimeField::new('lastLogin');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $deletedAt = DateTimeField::new('deletedAt');
        $author = AssociationField::new('author');

        if (Crud::PAGE_INDEX === $pageName) {
            $nameComplete = TextareaField::new('NameComplete');

            return [$id, $username, $nameComplete, $email, $roles, $enable, $lastLogin];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $username, $roles, $password, $surname, $email, $lastLogin, $name, $enable, $createdAt, $updatedAt, $deletedAt, $schoolAccessRight, $author];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$username, $plainPassword, $name, $surname, $email, $roles, $schoolAccessRight, $enable];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$username, $plainPassword, $name, $surname, $email, $roles, $schoolAccessRight, $enable];
        }
    }
}
