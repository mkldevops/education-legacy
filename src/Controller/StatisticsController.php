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

class StatisticsController extends AbstractController
{
    /**
     * @throws ORMException
     * @throws AppException
     */
    #[Route(path: '/statistics/number-students', name: 'app_statistics_number_students', methods: ['GET'])]
    public function numberStudents(StudentRepository $studentRepository, SchoolManager $schoolManager): Response
    {
        $data = $studentRepository->getStatsNumberStudent($schoolManager->getSchool());

        return $this->render('statistics/number_students.html.twig', ['data' => $data]);
    }

    /**
     * @throws AppException
     */
    #[Route(path: '/statistics/account', name: 'app_statistics_account', methods: ['GET'])]
    public function statsAccount(AccountRepository $accountRepository, SchoolManager $schoolManager): Response
    {
        $data = $accountRepository->getStatsAccount($schoolManager->getSchool(), true);

        return $this->render('statistics/stats_account.html.twig', ['data' => $data]);
    }
}
