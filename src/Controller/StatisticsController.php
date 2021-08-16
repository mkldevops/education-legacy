<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractBaseController;
use App\Entity\Account;
use App\Entity\Student;
use Doctrine\ORM\ORMException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Hamada Sidi Fahari <h.fahari@gmail.com>
 *
 * @Route("/statistics")
 */
class StatisticsController extends AbstractBaseController
{
    /**
     * Number Students.
     *
     * @Route("/number-students", name="app_statistics_numberstudents", methods={"GET"})
     *
     *
     * @throws ORMException
     * @Template()
     * @return array<string, mixed>
     */
    public function numberStudents(): array
    {
        $data = $this
            ->getDoctrine()->getManager()
            ->getRepository(Student::class)
            ->getStatsNumberStudent($this->getSchool());

        return ['data' => $data];
    }

    /**
     * statsAccountAction.
     *
     * @Route("/account", name="app_statistics_account", methods={"GET"})
     *
     * @Template()
     * @return array<string, mixed>
     */
    public function statsAccount(): array
    {
        $data = $this
            ->getDoctrine()->getManager()
            ->getRepository(Account::class)
            ->getStatsAccount($this->getSchool(), true);

        return ['data' => $data];
    }
}
