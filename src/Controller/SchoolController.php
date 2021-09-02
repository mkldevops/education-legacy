<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractBaseController;
use App\Entity\School;
use App\Exception\AppException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_TEACHER')]
#[Route(path: '/school')]
class SchoolController extends AbstractBaseController
{
    /**
     * @throws AppException
     */
    #[Route(path: '/switch/{id}', name: 'app_school_switch', methods: ['GET'])]
    public function switch(Request $request, School $school): RedirectResponse
    {
        $this->schoolManager->switch($school);
        $this->addFlash('success', $this->trans('flash.switch_school.success', ['%name' => $school], 'school'));
        if (!$referer = $request->headers->get('referer')) {
            throw new AppException('Referer page not defined');
        }

        return new RedirectResponse($referer);
    }
}
