<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\AppException;
use App\Repository\AccountRepository;
use App\Repository\StudentRepository;
use Symfony\Component\HttpFoundation\Response;
use App\Controller\Base\AbstractBaseController;
use App\Entity\Account;
use App\Entity\Student;
use Doctrine\ORM\ORMException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/statistics')]
class StatisticsController extends AbstractBaseController
{
    /**
     * @throws ORMException
     * @throws AppException
     */
    #[Route(path: '/number-students', name: 'app_statistics_numberstudents', methods: ['GET'])]
    public function numberStudents(StudentRepository $repository): Response
    {
        $data = $repository->getStatsNumberStudent($this->getSchool());
        return $this->render('statistics/number_students.html.twig', ['data' => $data]);
    }

    /**
     * @throws AppException
     */
    #[Route(path: '/account', name: 'app_statistics_account', methods: ['GET'])]
    public function statsAccount(AccountRepository $repository): Response
    {
        $data = $repository->getStatsAccount($this->getSchool(), true);
        return $this->render('statistics/stats_account.html.twig', ['data' => $data]);
    }
}
