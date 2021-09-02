<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use App\Exception\AppException;
use Doctrine\Persistence\ObjectManager;

/**
 * Class Users.
 *
 * @author fardus
 */
class UserFixtures extends AbstractAppFixtures
{
    /**
     * @throws AppException
     */
    public function load(ObjectManager $manager): void
    {
        foreach (self::getData() as $i => $item) {
            // On crée l'utilisateur
            $entity = (new User())
                ->setUsername($item['username'])
                ->setEmail($item['email'])
                ->setName($item['name'])
                ->setSurname($item['surname'])
                ->setPassword($item['password'])
                ->setEnable($item['enable']);

            if (is_array($item['roles'])) {
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
