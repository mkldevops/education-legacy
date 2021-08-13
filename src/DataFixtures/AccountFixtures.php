<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Account;
use App\Entity\Structure;
use App\Exception\AppException;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Class Schools.
 *
 * @author  fardus
 */
class AccountFixtures extends AppFixtures implements DependentFixtureInterface
{
    /**
     * @throws AppException
     */
    public function load(ObjectManager $manager): void
    {
        foreach (self::getData() as $i => $data) {
            /** @var Structure $structure */
            $structure = $this->getReference(StructureFixtures::getKey($data['structure']));

            $account = (new Account())
                ->setId($data['id'])
                ->setName($data['name'])
                ->setEnable($data['status'])
                ->setPrincipal($data['principal'])
                ->setIsBank($data['isBank'])
                ->setEnableAccountStatement($data['enableAccountStatement'])
                ->setStructure($structure);

            $manager->persist($account);
            $manager->flush();

            $this->addReference(self::getKey($i), $account);
        }
    }

    public function getDependencies(): array
    {
        return [
            StructureFixtures::class,
        ];
    }
}
