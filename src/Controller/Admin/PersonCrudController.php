<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Person;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
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
            ->setSearchFields(['id', 'forname', 'phone', 'email', 'birthplace', 'gender', 'address', 'zip', 'city', 'name']);
    }

    public function configureFields(string $pageName): iterable
    {
        $forname = TextField::new('forname');
        $phone = TextField::new('phone');
        $email = TextField::new('email');
        $birthday = DateTimeField::new('birthday');
        $birthplace = TextField::new('birthplace');
        $gender = TextField::new('gender');
        $address = TextField::new('address');
        $zip = TextField::new('zip');
        $city = TextField::new('city');
        $name = TextField::new('name');
        $enable = Field::new('enable');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $deletedAt = DateTimeField::new('deletedAt');
        $user = AssociationField::new('user');
        $member = AssociationField::new('member');
        $student = AssociationField::new('student');
        $schools = AssociationField::new('schools');
        $image = AssociationField::new('image');
        $family = AssociationField::new('family');
        $author = AssociationField::new('author');
        $id = TextField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $forname, $phone, $email, $birthday, $birthplace, $gender];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $forname, $phone, $email, $birthday, $birthplace, $gender, $address, $zip, $city, $name, $enable, $createdAt, $updatedAt, $deletedAt, $user, $member, $student, $schools, $image, $family, $author];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$forname, $phone, $email, $birthday, $birthplace, $gender, $address, $zip, $city, $name, $enable, $createdAt, $updatedAt, $deletedAt, $user, $member, $student, $schools, $image, $family, $author];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$forname, $phone, $email, $birthday, $birthplace, $gender, $address, $zip, $city, $name, $enable, $createdAt, $updatedAt, $deletedAt, $user, $member, $student, $schools, $image, $family, $author];
        }
    }
}
