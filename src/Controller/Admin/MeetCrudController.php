<?php

namespace App\Controller\Admin;

use App\Entity\Meet;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class MeetCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Meet::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
