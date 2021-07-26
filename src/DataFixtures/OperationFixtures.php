<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Operation;
use App\Exception\AppException;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class OperationFixtures extends AppFixtures implements DependentFixtureInterface
{
    /**
     * @throws AppException
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 1; $i <= 10; ++$i) {
            $entity = (new Operation())
                ->setAccount($this->getReference(AccountFixtures::getKey(random_int(1, 2))))
                ->setName($faker->title)
                ->setAmount($faker->randomFloat())
                ->setDate($faker->dateTime)
                ->setReference($faker->text(10))
                ->setOperationGender($this->getReference(OperationGenderFixtures::getKey(random_int(1, 9))))
                ->setTypeOperation($this->getReference(TypeOperationFixtures::getKey(random_int(0, 19))))
                ->setAuthor($this->getReference(UserFixtures::getKey(0)))
            ;

            $manager->persist($entity);
            $manager->flush();
        }
    }

    /**
     * @return string[]
     */
    public function getDependencies()
    {
        return [
            TypeOperationFixtures::class,
            AccountFixtures::class,
            OperationGenderFixtures::class,
            UserFixtures::class,
        ];
    }
}
