<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Student;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class StudentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Student::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Student')
            ->setEntityLabelInPlural('Student')
            ->setSearchFields([
                'id',
                'person.name',
                'person.forname',
                'person.id',
                'school',
                'lastSchool',
                'personAuthorized',
                'remarksHealth',
            ])
        ;
    }

    public function configureFields(string $pageName): \Iterator
    {
        yield IntegerField::new('id', 'ID');
        yield TextField::new('person.name');
        yield TextField::new('person.forname');
        yield IntegerField::new('age');
        yield AssociationField::new('grade');
        yield TextField::new('lastSchool');
        yield BooleanField::new('letAlone');
        yield BooleanField::new('enable');
        yield AssociationField::new('school');
        yield TextField::new('personAuthorized')->hideOnIndex();
        yield TextareaField::new('remarksHealth')->hideOnIndex();
        yield DateTimeField::new('dateRegistration');
        yield DateTimeField::new('dateDesactivated');
        yield AssociationField::new('person')->hideOnIndex();
        yield AssociationField::new('classPeriods');
        yield AssociationField::new('packagePeriods');
        yield AssociationField::new('appealCourses');
        yield AssociationField::new('comments')->hideOnIndex();
        yield AssociationField::new('author');
        yield DateTimeField::new('createdAt')->onlyOnDetail();
        yield DateTimeField::new('updatedAt')->onlyOnDetail();
        yield DateTimeField::new('deletedAt')->onlyOnDetail();
    }
}
