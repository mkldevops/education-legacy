<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use App\Exception\AppException;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends AbstractAppFixtures
{
    /**
     * @var string
     */
    public const EMAIL = 'h.fahari@gmail.com';

    public function __construct(
        private UserPasswordHasherInterface $hasher
    ) {
    }

    /**
     * @throws AppException
     */
    public function load(ObjectManager $manager): void
    {
        foreach (self::getData() as $i => $item) {
            // On crée l'utilisateur
            $entity = new User();
            $entity
                ->setUsername($item['username'])
                ->setEmail($item['email'])
                ->setName($item['name'])
                ->setSurname($item['surname'])
                ->setPassword($this->hasher->hashPassword($entity, $item['password']))
                ->setEnable((bool) $item['enable'])
            ;

            if (\is_array($item['roles'])) {
                foreach ($item['roles'] as $role) {
                    $entity->addRole($role);
                }
            }

            $manager->persist($entity);
            $manager->flush();

            $this->addReference(self::getKey($i), $entity);
        }
    }
}
