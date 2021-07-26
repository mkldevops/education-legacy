<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Package;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PackageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Package::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Package')
            ->setEntityLabelInPlural('Package')
            ->setSearchFields(['id', 'name', 'description', 'price']);
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name');
        $description = TextareaField::new('description');
        $price = NumberField::new('price');
        $status = Field::new('status');
        $school = AssociationField::new('school');
        $id = IntegerField::new('id', 'ID');
        $record = DateTimeField::new('record');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $name, $price, $record, $status, $school];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $name, $description, $price, $record, $status, $school];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$name, $description, $price, $status, $school];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$name, $description, $price, $status, $school];
        }
    }
}
