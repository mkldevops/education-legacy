<?php

namespace App\Controller;

use App\Controller\Base\BaseController;
use App\Entity\Period;
use App\Entity\School;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/school")
 * @IsGranted("ROLE_TEACHER")
 */
class SchoolController extends BaseController
{
    /**
     * @Route("/switch/{id}", name="app_school_switch", methods={"GET"})
     */
    public function switch(Request $request, School $school)
    {

        try {
            $this->schoolManager->switch($school);
            $this->addFlash('success', $this->trans('flash.switch_school.success', ['%name' => $school], 'school'));
        } catch (\Exception $e) {
            $this->addFlash('danger', $this->trans('flash.switch_school.failed', ['%name' => $school], 'school'));
        }

        return new RedirectResponse($request->headers->get('referer'));
    }
}
