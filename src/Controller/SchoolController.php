<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractBaseController;
use App\Entity\School;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_TEACHER")
 */
#[Route(path: '/school')]
class SchoolController extends AbstractBaseController
{
    #[Route(path: '/switch/{id}', name: 'app_school_switch', methods: ['GET'])]
    public function switch(Request $request, School $school): RedirectResponse
    {
        try {
            $this->schoolManager->switch($school);
            $this->addFlash('success', $this->trans('flash.switch_school.success', ['%name' => $school], 'school'));
        } catch (Exception $e) {
            $this->addFlash('danger', $this->trans('flash.switch_school.failed', ['%name' => $school], 'school'));
        }
        return new RedirectResponse($request->headers->get('referer'));
    }
}
