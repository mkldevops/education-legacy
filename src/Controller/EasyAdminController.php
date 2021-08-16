<?php

declare(strict_types=1);

namespace App\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController as AdminController;

class EasyAdminController extends AdminController
{
    public function persistEntity(object $entity): void
    {
        if (method_exists($entity, 'setAuthor')) {
            $entity->setAuthor($this->getUser());
        }
    }
}
