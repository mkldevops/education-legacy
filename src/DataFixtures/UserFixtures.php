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
    final public const EMAIL = 'h.fahari@gmail.com';

    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher
    ) {}

    /**
     * @throws AppException
     */
    public function load(ObjectManager $objectManager): void
    {
        foreach (self::getData() as $i => $item) {
            // On crée l'utilisateur
            $entity = new User();
            $entity
                ->setUsername($item['username'])
                ->setEmail($item['email'])
                ->setName($item['name'])
                ->setSurname($item['surname'])
                ->setPassword($this->userPasswordHasher->hashPassword($entity, $item['password']))
                ->setEnable((bool) $item['enable'])
            ;

            if (\is_array($item['roles'])) {
                foreach ($item['roles'] as $role) {
                    $entity->addRole($role);
                }
            }

            $objectManager->persist($entity);
            $objectManager->flush();

            $this->addReference(self::getKey($i), $entity);
        }
    }
}
