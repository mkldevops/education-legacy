<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Family;
use App\Entity\Person;
use PHPUnit\Framework\TestCase;

/**
 * Test unitaire pour vérifier la structure des familles.
 *
 * @internal
 *
 * @coversNothing
 */
final class FamilyFixturesTest extends TestCase
{
    public function testFamilyStructure(): void
    {
        // Créer une famille avec parents
        $father = new Person();
        $father->setForname('Pierre')
            ->setName('Dupont')
            ->setGender(Person::GENDER_MALE)
            ->setPhone('0142345678')
            ->setEmail('pierre.dupont@email.fr')
            ->setBirthday(new \DateTime('1985-04-15'))
            ->setBirthplace('Paris')
        ;

        $mother = new Person();
        $mother->setForname('Marie')
            ->setName('Dupont')
            ->setGender(Person::GENDER_FEMALE)
            ->setPhone('0143456789')
            ->setEmail('marie.dupont@email.fr')
            ->setBirthday(new \DateTime('1987-08-22'))
            ->setBirthplace('Lyon')
        ;

        $family = new Family();
        $family->setEmail('famille.dupont@email.fr')
            ->setAddress('15 Avenue des Champs')
            ->setZip('75001')
            ->setCity('Paris')
            ->setLanguage('fr')
            ->setNumberChildren(2)
            ->setPersonAuthorized('Marie Dupont (mère), Pierre Dupont (père)')
            ->setPersonEmergency('Dr. Laurent Martin - 01 42 34 56 78')
            ->setFather($father)
            ->setMother($mother)
        ;

        // Tests sur la famille
        self::assertSame('famille.dupont@email.fr', $family->getEmail());
        self::assertSame('Paris', $family->getCity());
        self::assertSame(2, $family->getNumberChildren());
        self::assertSame('fr', $family->getLanguage());

        // Tests sur les parents
        self::assertNotNull($family->getFather());
        self::assertNotNull($family->getMother());
        self::assertSame('Pierre', $family->getFather()->getForname());
        self::assertSame('Marie', $family->getMother()->getForname());
        self::assertSame(Person::GENDER_MALE, $family->getFather()->getGender());
        self::assertSame(Person::GENDER_FEMALE, $family->getMother()->getGender());

        // Test du nom complet de la famille
        $nameComplete = $family->getNameComplete();
        self::assertStringContainsString('Marie', $nameComplete);
        self::assertStringContainsString('Pierre', $nameComplete);
    }

    public function testFamilyWithChild(): void
    {
        // Créer un enfant
        $child = new Person();
        $child->setForname('Antoine')
            ->setName('Dupont')
            ->setGender(Person::GENDER_MALE)
            ->setPhone('0123456789')
            ->setBirthday(new \DateTime('2015-03-15'))
            ->setBirthplace('Paris')
        ;

        // Créer une famille
        $family = new Family();
        $family->setNumberChildren(1);

        // Associer l'enfant à la famille
        $child->setFamily($family);

        // Vérifications
        self::assertSame($family, $child->getFamily());
        self::assertSame(1, $family->getNumberChildren());
    }

    public function testPersonAuthorizedAndEmergency(): void
    {
        $family = new Family();
        $family->setPersonAuthorized('Marie Dupont (mère), Pierre Dupont (père)')
            ->setPersonEmergency('Dr. Laurent Martin - 01 42 34 56 78')
        ;

        self::assertSame('Marie Dupont (mère), Pierre Dupont (père)', $family->getPersonAuthorized());
        self::assertSame('Dr. Laurent Martin - 01 42 34 56 78', $family->getPersonEmergency());
    }
}
