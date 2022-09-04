<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\School;
use App\Exception\AppException;
use App\Manager\SchoolManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('ROLE_TEACHER')]
#[Route(path: '/school')]
class SchoolController extends AbstractController
{
    /**
     * @throws AppException
     */
    #[Route(path: '/switch/{id}', name: 'app_school_switch', methods: ['GET'])]
    public function switch(Request $request, School $school, SchoolManager $schoolManager, TranslatorInterface $translator): RedirectResponse
    {
        $schoolManager->switch($school);
        $this->addFlash('success', $translator->trans('flash.switch_school.success', ['%name' => $school], 'school'));
        if (!$referer = $request->headers->get('referer')) {
            throw new AppException('Referer page not defined');
        }

        return new RedirectResponse($referer);
    }
}
