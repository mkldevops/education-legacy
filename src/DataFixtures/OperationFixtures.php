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
    public function load(ObjectManager $objectManager): void
    {
        $generator = Factory::create();

        $account = $this->getReference(AccountFixtures::getKey(random_int(1, 2)), Account::class);

        $operationGender = $this->getReference(OperationGenderFixtures::getKey(random_int(1, 9)), OperationGender::class);

        $typeOperation = $this->getReference(TypeOperationFixtures::getKey(random_int(0, 19)), TypeOperation::class);

        $user = $this->getReference(UserFixtures::getKey(0), User::class);

        for ($i = 1; $i <= 10; ++$i) {
            $entity = (new Operation())
                ->setAccount($account)
                ->setName($generator->title())
                ->setAmount($generator->randomFloat())
                ->setDate($generator->dateTime())
                ->setReference($generator->text(10))
                ->setOperationGender($operationGender)
                ->setTypeOperation($typeOperation)
                ->setAuthor($user)
            ;

            $objectManager->persist($entity);
            $objectManager->flush();
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
