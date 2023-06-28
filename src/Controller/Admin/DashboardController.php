<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ClassPeriod;
use App\Entity\ClassSchool;
use App\Entity\Course;
use App\Entity\Document;
use App\Entity\Grade;
use App\Entity\Meet;
use App\Entity\Member;
use App\Entity\OperationGender;
use App\Entity\Package;
use App\Entity\Period;
use App\Entity\Person;
use App\Entity\School;
use App\Entity\TypeOperation;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator
    ) {
    }

    #[Route('/admin')]
    public function index(): Response
    {
        return $this->redirect($this->adminUrlGenerator->setController(StudentCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Education')
        ;
    }

    public function configureActions(): Actions
    {
        return parent::configureActions()
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(
                Crud::PAGE_INDEX,
                Action::DETAIL,
                static fn(Action $action) => $action->setIcon('fa fa-file-alt')->setLabel(false)
            )->update(
                Crud::PAGE_INDEX,
                Action::DELETE,
                static fn(Action $action) => $action->setIcon('fa fa-trash')->setLabel(false)
            )->update(
                Crud::PAGE_INDEX,
                Action::EDIT,
                static fn(Action $action) => $action->setIcon('fa fa-edit')->setLabel(false)
            )
            ->setPermission(Action::NEW, 'ROLE_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN')
        ;
    }

    public function configureMenuItems(): iterable
    {
        $submenu1 = [
            MenuItem::linkToCrud('ClassSchool', '', ClassSchool::class),
            MenuItem::linkToCrud('ClassPeriod', '', ClassPeriod::class),
        ];

        yield MenuItem::linktoRoute('Dashboard', 'fas fa-folder-open', 'app_admin_home');

        yield MenuItem::section('Education', 'fas fa-folder-open');
        yield MenuItem::linkToCrud('Course', 'fas fa-folder-open', Course::class);
        yield MenuItem::linkToCrud('Grade', 'fas fa-folder-open', Grade::class);
        yield MenuItem::linkToCrud('Package', 'fas fa-folder-open', Package::class);
        yield MenuItem::subMenu('Classes manage', 'fas fa-folder-open')->setSubItems($submenu1);
        yield MenuItem::linkToCrud('School', 'fas fa-folder-open', School::class);

        yield MenuItem::section('Accountable', 'fas fa-folder-open');
        yield MenuItem::linkToCrud('TypeOperation', 'fas fa-folder-open', TypeOperation::class);
        yield MenuItem::linkToCrud('OperationGender', 'fas fa-folder-open', OperationGender::class);

        yield MenuItem::section('Parameters', 'fas fa-folder-open');
        yield MenuItem::linkToCrud('Period', 'fas fa-folder-open', Period::class);
        yield MenuItem::linkToCrud('Document', 'fas fa-folder-open', Document::class);

        yield MenuItem::section('Persons manage', 'fas fa-folder-open');
        yield MenuItem::linkToCrud('User', 'fas fa-folder-open', User::class);
        yield MenuItem::linkToCrud('Person', 'fas fa-folder-open', Person::class);
        yield MenuItem::linkToCrud('Member', 'fas fa-folder-open', Member::class);
        yield MenuItem::linkToCrud('Réunions', 'fas fa-folder-open', Meet::class);
    }
}
