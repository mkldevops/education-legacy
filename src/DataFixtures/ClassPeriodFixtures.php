<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\ClassPeriod;
use App\Entity\ClassSchool;
use App\Entity\Period;
use App\Entity\User;
use App\Exception\AppException;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class ClassPeriodFixtures extends AbstractAppFixtures implements DependentFixtureInterface
{
    /**
     * @throws AppException
     */
    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)->findAll();
        $defaultUser = !empty($users) ? $users[0] : null;

        // Récupérer la première période active (celle qui est active maintenant)
        $period = $manager->getRepository(Period::class)->findOneBy(['enable' => true]);
        if (!$period) {
            // Si aucune période active, prendre la première
            $period = $manager->getRepository(Period::class)->findOneBy([]);
        }

        if (!$period) {
            throw new \RuntimeException('Aucune période trouvée dans la base de données');
        }

        foreach (self::getData() as $id => $data) {
            $classPeriod = new ClassPeriod();

            // Récupérer la classe par référence
            $classSchool = $this->getReference(ClassSchoolFixtures::getKey($data['classSchool']), ClassSchool::class);

            $classPeriod->setClassSchool($classSchool)
                ->setPeriod($period)
                ->setComment($data['comment'])
                ->setEnable($data['enable'])
            ;

            // Définir l'auteur (utilisateur par défaut)
            if ($defaultUser) {
                $classPeriod->setAuthor($defaultUser);
            }

            $manager->persist($classPeriod);
            $this->addReference(self::getKey($id), $classPeriod);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ClassSchoolFixtures::class,
            PeriodFixtures::class,
            UserFixtures::class,
        ];
    }
}
