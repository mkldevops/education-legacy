<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Account;
use App\Entity\Operation;
use App\Entity\OperationGender;
use App\Entity\TypeOperation;
use App\Entity\User;
use App\Exception\AppException;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class OperationFixtures extends AbstractAppFixtures implements DependentFixtureInterface
{
    /**
     * @throws AppException
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        /** @var Account $account */
        $account = $this->getReference(AccountFixtures::getKey(random_int(1, 2)));

        /** @var OperationGender $operationGender */
        $operationGender = $this->getReference(OperationGenderFixtures::getKey(random_int(1, 9)));

        /** @var TypeOperation $typeOperation */
        $typeOperation = $this->getReference(TypeOperationFixtures::getKey(random_int(0, 19)));

        /** @var User $user */
        $user = $this->getReference(UserFixtures::getKey(0));

        for ($i = 1; $i <= 10; ++$i) {
            $entity = (new Operation())
                ->setAccount($account)
                ->setName($faker->title())
                ->setAmount($faker->randomFloat())
                ->setDate($faker->dateTime())
                ->setReference($faker->text(10))
                ->setOperationGender($operationGender)
                ->setTypeOperation($typeOperation)
                ->setAuthor($user)
            ;

            $manager->persist($entity);
            $manager->flush();
        }
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            TypeOperationFixtures::class,
            AccountFixtures::class,
            OperationGenderFixtures::class,
            UserFixtures::class,
        ];
    }
}
