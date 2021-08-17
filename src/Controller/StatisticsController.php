<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use App\Controller\Base\AbstractBaseController;
use App\Entity\Account;
use App\Entity\Student;
use Doctrine\ORM\ORMException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Hamada Sidi Fahari <h.fahari@gmail.com>
 */
#[Route(path: '/statistics')]
class StatisticsController extends AbstractBaseController
{
    /**
     * Number Students.
     *
     *
     * @throws ORMException
     */
    #[Route(path: '/number-students', name: 'app_statistics_numberstudents', methods: ['GET'])]
    public function numberStudents(): Response
    {
        $data = $this
            ->getDoctrine()->getManager()
            ->getRepository(Student::class)
            ->getStatsNumberStudent($this->getSchool());
        return $this->render('Statistics/numberStudents.html.twig', ['data' => $data]);
    }
    /**
     * statsAccountAction.
     *
     *
     */
    #[Route(path: '/account', name: 'app_statistics_account', methods: ['GET'])]
    public function statsAccount(): Response
    {
        $data = $this
            ->getDoctrine()->getManager()
            ->getRepository(Account::class)
            ->getStatsAccount($this->getSchool(), true);
        return $this->render('Statistics/statsAccount.html.twig', ['data' => $data]);
    }
}
