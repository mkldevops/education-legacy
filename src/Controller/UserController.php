<?php

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
    /**
     * @var UserPasswordEncoderInterface
     */
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @param User $entity
     */
    public function persistEntity($entity): void
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

    /**
     * @param object $entity
     */
    public function updateEntity($entity): void
    {
        $this->encodePassword($entity);
    }

    /**
     * @Route("/profile", name="app_user_profile")
     */
    public function profile()
    {
        return $this->render('user/profile.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
}
