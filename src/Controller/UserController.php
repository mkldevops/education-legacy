<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/user')]
class UserController extends EasyAdminController
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function persistEntity(User $entity): void
    {
        $this->encodePassword($entity);
    }

    public function encodePassword(User $user): void
    {
        if (empty($user->getPassword())) {
            return;
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPlainPassword()));
    }

    #[Route(path: '/profile', name: 'app_user_profile')]
    public function profile(): Response
    {
        return $this->render('user/profile.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
}
