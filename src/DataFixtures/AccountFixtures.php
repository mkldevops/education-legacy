<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Account;
use App\Entity\Structure;
use App\Exception\AppException;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AccountFixtures extends AbstractAppFixtures implements DependentFixtureInterface
{
    /**
     * @throws AppException
     */
    public function load(ObjectManager $objectManager): void
    {
        foreach (self::getData() as $i => $data) {
            $structure = $this->getReference(StructureFixtures::getKey($data['structure']), Structure::class);

            $account = (new Account())
                ->setId($data['id'])
                ->setName($data['name'])
                ->setEnable($data['status'])
                ->setPrincipal($data['principal'])
                ->setIsBank($data['isBank'])
                ->setEnableAccountStatement($data['enableAccountStatement'])
                ->setStructure($structure)
            ;

            $objectManager->persist($account);
            $objectManager->flush();

            $this->addReference(self::getKey($i), $account);
        }
    }

    /**
     * @return array<class-string<StructureFixtures>>
     */
    public function getDependencies(): array
    {
        return [
            StructureFixtures::class,
        ];
    }
}
