<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Grade;
use App\Entity\Person;
use App\Entity\School;
use App\Entity\Student;
use App\Exception\AppException;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class StudentFixtures extends AbstractAppFixtures implements DependentFixtureInterface
{
    /**
     * @throws AppException
     */
    public function load(ObjectManager $objectManager): void
    {
        foreach (self::getData() as $id => $data) {
            // Récupérer les références nécessaires
            $grade = $this->getReference(GradeFixtures::getKey($data['grade']), Grade::class);
            $school = $this->getReference(SchoolFixtures::getKey($data['school']), School::class);

            // Créer l'entité Person
            $person = new Person();
            $person->setForname($data['forname'])
                ->setName($data['name'])
                ->setGender($data['gender'])
                ->setBirthday(new \DateTime($data['birthday']))
                ->setBirthplace($data['birthplace'])
                ->setPhone($data['phone'])
                ->setEmail($data['email'])
                ->setEnable(true)
                ->setCreatedAt(new \DateTime())
            ;

            // Créer l'entité Student
            $student = new Student();
            $student->setPerson($person)
                ->setGrade($grade)
                ->setSchool($school)
                ->setLastSchool($data['lastSchool'])
                ->setPersonAuthorized($data['personAuthorized'])
                ->setRemarksHealth($data['remarksHealth'])
                ->setLetAlone($data['letAlone'])
                ->setEnable(true)
                ->setDateRegistration(new \DateTime())
                ->setCreatedAt(new \DateTime())
            ;

            $objectManager->persist($person);
            $objectManager->persist($student);
            $objectManager->flush();

            $this->addReference(self::getKey($id), $student);
        }
    }

    /**
     * @return class-string[]
     */
    public function getDependencies(): array
    {
        return [
            GradeFixtures::class,
            SchoolFixtures::class,
        ];
    }
}
