<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Package;
use App\Entity\School;
use App\Exception\AppException;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class PackageFixtures extends AbstractAppFixtures implements DependentFixtureInterface
{
    /**
     * @throws AppException
     */
    public function load(ObjectManager $manager): void
    {
        foreach (self::getData() as $id => $data) {
            $package = new Package();

            $package->setName($data['name'])
                ->setDescription($data['description'])
                ->setPrice($data['price'])
                ->setEnable($data['enable'])
            ;

            // Associer l'école si elle est définie
            if (isset($data['school']) && !empty($data['school'])) {
                $school = $this->getReference(SchoolFixtures::getKey($data['school']), School::class);
                $package->setSchool($school);
            }

            $manager->persist($package);
            $this->addReference(self::getKey($id), $package);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SchoolFixtures::class,
        ];
    }
}
