<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Family;
use App\Entity\Person;
use App\Exception\AppException;
use Doctrine\Persistence\ObjectManager;

class FamilyFixtures extends AbstractAppFixtures
{
    /**
     * @throws AppException
     */
    public function load(ObjectManager $objectManager): void
    {
        foreach (self::getData() as $id => $data) {
            // Créer l'entité Family
            $family = new Family();
            $family->setEmail($data['email'])
                ->setAddress($data['address'])
                ->setZip($data['zip'])
                ->setCity($data['city'])
                ->setLanguage($data['language'])
                ->setNumberChildren($data['numberChildren'])
                ->setPersonAuthorized($data['personAuthorized'])
                ->setPersonEmergency($data['personEmergency'])
                ->setEnable(true)
                ->setCreatedAt(new \DateTime())
            ;

            // Créer le père si défini
            if (isset($data['father'])) {
                $father = $this->createPersonFromData($data['father'], Person::GENDER_MALE);
                $family->setFather($father);
            }

            // Créer la mère si définie
            if (isset($data['mother'])) {
                $mother = $this->createPersonFromData($data['mother'], Person::GENDER_FEMALE);
                $family->setMother($mother);
            }

            // Créer le tuteur légal si défini
            if (isset($data['legalGuardian'])) {
                $legalGuardian = $this->createPersonFromData($data['legalGuardian'], $data['legalGuardian']['gender'] ?? Person::GENDER_FEMALE);
                $family->setLegalGuardian($legalGuardian);
            }

            $objectManager->persist($family);
            $objectManager->flush();

            $this->addReference(self::getKey($id), $family);
        }
    }

    /**
     * Crée une entité Person à partir des données.
     */
    private function createPersonFromData(array $personData, string $gender): Person
    {
        $person = new Person();
        $person->setForname($personData['forname'])
            ->setName($personData['name'])
            ->setGender($gender)
            ->setPhone($personData['phone'])
            ->setEmail($personData['email'])
            ->setBirthday(new \DateTime($personData['birthday']))
            ->setBirthplace($personData['birthplace'])
            ->setEnable(true)
            ->setCreatedAt(new \DateTime())
        ;

        return $person;
    }
}
