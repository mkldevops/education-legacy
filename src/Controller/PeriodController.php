<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\BaseController;
use App\Entity\Period;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/period')]
class PeriodController extends BaseController
{
    #[Route('/switch/{id}', name: 'app_period_switch', methods: ['GET'])]
    public function switch(Request $request, Period $period): RedirectResponse
    {
        $periodSession = $request->getSession()->get('period');
        $periodSession->selected = $period;
        $this->get('session')->set(Period::class, $periodSession);

        $this->addFlash('success', $this->trans('flash.change_period.success', ['%name' => $period], Period::class));

        return new RedirectResponse($request->headers->get('referer'));
    }
}
