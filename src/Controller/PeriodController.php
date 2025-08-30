<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Period;
use App\Exception\AppException;
use App\Manager\PeriodManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PeriodController extends AbstractController
{
    /**
     * @throws AppException
     */
    #[Route('/period/switch/{id}', name: 'app_period_switch', methods: [Request::METHOD_GET])]
    public function switch(Request $request, Period $period, PeriodManager $periodManager, TranslatorInterface $translator): RedirectResponse
    {
        $periodManager->switch($period);

        $this->addFlash('success', $translator->trans('flash.change_period.success', ['%name' => $period], Period::class));

        return new RedirectResponse($request->headers->get('referer'));
    }
}
