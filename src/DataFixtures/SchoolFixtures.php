<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\School;
use App\Entity\Structure;
use App\Entity\User;
use App\Exception\AppException;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Class Schools.
 *
 * @author  fardus
 */
class SchoolFixtures extends AbstractAppFixtures implements DependentFixtureInterface
{
    /**
     * @throws AppException
     */
    public function load(ObjectManager $objectManager): void
    {
        foreach (self::getData() as $i => $data) {
            $structure = $this->getReference(StructureFixtures::getKey($data['structure']), Structure::class);

            $school = (new School())
                ->setName($data['name'])
                ->setCity($data['city'])
                ->setZip($data['zip'])
                ->setEnable(true)
                ->setPrincipal($data['principal'])
                ->setStructure($structure)
            ;

            $objectManager->persist($school);
            $objectManager->flush();

            $this->addReference(self::getKey($i), $school);
        }

        $users = $objectManager->getRepository(User::class)->findAll();
        $schools = $objectManager->getRepository(School::class)->findAll();

        foreach ($users as $user) {
            foreach ($schools as $school) {
                $user->addschoolAccessRight($school);

                $objectManager->flush();
            }
        }
    }

    /**
     * @return class-string<StructureFixtures>[]|class-string<UserFixtures>[]
     */
    public function getDependencies(): array
    {
        return [
            StructureFixtures::class,
            UserFixtures::class,
        ];
    }
}
