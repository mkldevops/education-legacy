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
class SchoolFixtures extends AppFixtures implements DependentFixtureInterface
{
    /**
     * @throws AppException
     */
    public function load(ObjectManager $manager): void
    {
        foreach (self::getData() as $i => $data) {
            /** @var Structure $structure */
            $structure = $this->getReference(StructureFixtures::getKey($data['structure']));

            $school = (new School())
                ->setName($data['name'])
                ->setCity($data['city'])
                ->setZip($data['zip'])
                ->setEnable(true)
                ->setPrincipal($data['principal'])
                ->setStructure($structure);

            $manager->persist($school);
            $manager->flush();

            $this->addReference(self::getKey($i), $school);
        }

        $users = $manager->getRepository(User::class)->findAll();
        $schools = $manager->getRepository(School::class)->findAll();

        foreach ($users as $user) {
            foreach ($schools as $school) {
                $user->addschoolAccessRight($school);

                $manager->flush();
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
