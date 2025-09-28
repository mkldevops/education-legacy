<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\ClassSchool;
use App\Entity\School;
use App\Entity\User;
use App\Exception\AppException;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class ClassSchoolFixtures extends AbstractAppFixtures implements DependentFixtureInterface
{
    /**
     * @throws AppException
     */
    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)->findAll();
        $defaultUser = empty($users) ? null : $users[0];

        foreach (self::getData() as $id => $data) {
            $classSchool = new ClassSchool();

            $classSchool->setName($data['name'])
                ->setDescription($data['description'])
                ->setAgeMinimum($data['ageMinimum'])
                ->setAgeMaximum($data['ageMaximum'])
                ->setEnable($data['enable'])
            ;

            // Associer l'école si elle est définie
            if (isset($data['school']) && !empty($data['school'])) {
                $school = $this->getReference(SchoolFixtures::getKey($data['school']), School::class);
                $classSchool->setSchool($school);
            }

            // Définir l'auteur (utilisateur par défaut)
            if ($defaultUser) {
                $classSchool->setAuthor($defaultUser);
            }

            $manager->persist($classSchool);
            $this->addReference(self::getKey($id), $classSchool);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SchoolFixtures::class,
            UserFixtures::class,
        ];
    }
}
