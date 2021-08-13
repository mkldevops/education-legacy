<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractBaseController;
use App\Entity\Account;
use App\Entity\Operation;
use App\Entity\Person;
use App\Entity\Structure;
use App\Entity\Student;
use App\Exception\AppException;
use App\Exception\InvalidArgumentException;
use App\Manager\DashboardManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('')]
class AdminController extends AbstractBaseController
{
    #[Route('/search', name: 'app_admin_seacrh', methods: ['GET', 'POST'])]
    public function search(Request $request, DashboardManager $dashboard): Response
    {
        $search = $request->get('search');
        $result = $dashboard->search($search);

        return $this->render('admin/search.html.twig', ['result' => $result, 'search' => $search]);
    }

    /**
     * @throws InvalidArgumentException|AppException
     */
    #[Route('', name: 'app_admin_home')]
    public function index(DashboardManager $dashboard, TranslatorInterface $translator): Response
    {
        $data = (object)['student' => false];
        $manager = $this->getDoctrine()->getManager();

        $structure = $this->getEntitySchool()->getStructure();

        if ((!$structure instanceof Structure) || empty($structure->getAccounts()->count())) {
            $this->addFlash('danger', $translator->trans('account.not_exists', [
                '%link%' => $this->generateUrl('app_account_new'),
            ], 'account'));
        }

        $data->chartNbStudents = $dashboard->highChartStatsNumberStudents($this->getPeriod(), $this->getSchool());
        $data->student = $manager->getRepository(Student::class)->getStatsStudent($this->getSchool());
        $data->account = $manager->getRepository(Account::class)->getStatsAccount($this->getSchool());
        $data->lastOperations = $manager->getRepository(Operation::class)->getLastOperation($this->getSchool());

        return $this->render('admin/index.html.twig', ['data' => $data]);
    }

    public function menuInspina(Request $request): Response
    {
        $response = new Response();
        $response->setSharedMaxAge(60);
        $menus = DashboardManager::generateItemsOfMenu($request->get('route'));

        /** @var Person $person */
        $person = $this->getDoctrine()
            ->getRepository(Person::class)
            ->findOneBy(['user' => $this->getUser()->getId()]);

        return $this->render('admin/menu.html.twig', [
            'menus' => $menus,
            'person' => $person,
        ], $response);
    }

    public function diskUsage(): Response
    {
        $total = disk_total_space('/');
        $free = disk_free_space('/');
        //$memory = memory_get_peak_usage();

        //dump($memory);
        return $this->render('admin/disk_usage.html.twig', [
            'free' => DashboardManager::size($free),
            'total' => DashboardManager::size($total),
            // 'memory' => $memory
        ]);
    }
}
