<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserController.
 *
 * @Route("/user")
 */
class UserController extends EasyAdminController
{
    public function __construct(private UserPasswordEncoderInterface $passwordEncoder)
    {
    }

    public function persistEntity(\App\Entity\User $entity): void
    {
        $this->encodePassword($entity);
    }

    /**
     * @param $user
     */
    public function encodePassword($user): void
    {
        if (!$user instanceof User || empty($user->getPlainPassword())) {
            return;
        }

        $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()));
    }

    public function updateEntity(object $entity): void
    {
        $this->encodePassword($entity);
    }

    /**
     * @Route("/profile", name="app_user_profile")
     */
    public function profile(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('user/profile.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
}
