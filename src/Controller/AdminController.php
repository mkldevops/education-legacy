<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Person;
use App\Entity\Structure;
use App\Exception\AppException;
use App\Exception\InvalidArgumentException;
use App\Fetcher\SessionFetcherInterface;
use App\Manager\DashboardManager;
use App\Repository\AccountRepository;
use App\Repository\OperationRepository;
use App\Repository\PersonRepository;
use App\Repository\StudentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminController extends AbstractController
{
    /**
     * @throws AppException
     */
    #[Route('/search', name: 'app_admin_seacrh', methods: ['GET', 'POST'])]
    public function search(Request $request, DashboardManager $dashboardManager): Response
    {
        $search = $request->get('search');
        $result = $dashboardManager->search($search);

        return $this->render('admin/search.html.twig', ['result' => $result, 'search' => $search]);
    }

    /**
     * @throws AppException|InvalidArgumentException
     * @throws \Exception
     */
    #[Route('', name: 'app_admin_home')]
    public function index(
        DashboardManager $dashboardManager,
        TranslatorInterface $translator,
        StudentRepository $studentRepository,
        AccountRepository $accountRepository,
        OperationRepository $operationRepository,
        SessionFetcherInterface $sessionFetcher,
    ): Response {
        $data = (object) ['student' => false];

        $structure = $sessionFetcher->getEntitySchoolOnSession()->getStructure();

        if ((!$structure instanceof Structure) || empty($structure->getAccounts()->count())) {
            $this->addFlash('danger', $translator->trans('account.not_exists', [
                '%link%' => $this->generateUrl('app_account_new'),
            ], 'account'));
        }

        $data->student = $studentRepository->getStatsStudent($sessionFetcher->getSchoolOnSession());
        $data->account = $accountRepository->getStatsAccount($sessionFetcher->getSchoolOnSession());
        $data->lastOperations = $operationRepository->getLastOperation($sessionFetcher->getSchoolOnSession());

        return $this->render('admin/index.html.twig', ['data' => $data]);
    }

    /**
     * @throws AppException
     */
    public function menuInspina(Request $request, PersonRepository $personRepository): Response
    {
        $response = new Response();
        $response->setSharedMaxAge(60);

        $menus = DashboardManager::generateItemsOfMenu($request->get('route'));

        /** @var Person $person */
        $person = $personRepository->findOneBy(['user' => $this->getUser()]);

        return $this->render('admin/menu.html.twig', [
            'menus' => $menus,
            'person' => $person,
        ], $response);
    }

    public function diskUsage(): Response
    {
        $total = disk_total_space('/');
        $free = disk_free_space('/');

        return $this->render('admin/disk_usage.html.twig', [
            'free' => DashboardManager::size($free),
            'total' => DashboardManager::size($total),
            // 'memory' => $memory
        ]);
    }
}
