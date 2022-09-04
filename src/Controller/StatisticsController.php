<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\AppException;
use App\Manager\SchoolManager;
use App\Repository\AccountRepository;
use App\Repository\StudentRepository;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/statistics')]
class StatisticsController extends AbstractController
{
    /**
     * @throws ORMException
     * @throws AppException
     */
    #[Route(path: '/number-students', name: 'app_statistics_number_students', methods: ['GET'])]
    public function numberStudents(StudentRepository $repository, SchoolManager $schoolManager): Response
    {
        $data = $repository->getStatsNumberStudent($schoolManager->getSchool());

        return $this->render('statistics/number_students.html.twig', ['data' => $data]);
    }

    /**
     * @throws AppException
     */
    #[Route(path: '/account', name: 'app_statistics_account', methods: ['GET'])]
    public function statsAccount(AccountRepository $repository, SchoolManager $schoolManager): Response
    {
        $data = $repository->getStatsAccount($schoolManager->getSchool(), true);

        return $this->render('statistics/stats_account.html.twig', ['data' => $data]);
    }
}
