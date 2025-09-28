<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Family;
use App\Entity\Person;
use App\Entity\Student;
use PHPUnit\Framework\TestCase;

/**
 * Test unitaire pour vérifier que les étudiants héritent correctement des téléphones familiaux.
 *
 * @internal
 *
 * @coversNothing
 */
final class StudentFamilyPhonesTest extends TestCase
{
    public function testStudentInheritsParentPhones(): void
    {
        // Créer les parents
        $father = new Person();
        $father->setForname('Pierre')
            ->setName('Dupont')
            ->setGender(Person::GENDER_MALE)
            ->setPhone('0142345678')
            ->setEmail('pierre.dupont@email.fr')
        ;

        $mother = new Person();
        $mother->setForname('Marie')
            ->setName('Dupont')
            ->setGender(Person::GENDER_FEMALE)
            ->setPhone('0143456789')
            ->setEmail('marie.dupont@email.fr')
        ;

        // Créer la famille
        $family = new Family();
        $family->setFather($father)
            ->setMother($mother)
            ->setEmail('famille.dupont@email.fr')
        ;

        // Créer l'enfant
        $child = new Person();
        $child->setForname('Antoine')
            ->setName('Dupont')
            ->setGender(Person::GENDER_MALE)
            ->setPhone('0123456789')
            ->setFamily($family)
        ;

        // Créer l'étudiant
        $student = new Student();
        $student->setPerson($child);

        // Vérifier que l'étudiant a accès à tous les téléphones
        $phones = $student->getListPhones();

        // L'étudiant devrait avoir son téléphone + ceux des parents
        self::assertGreaterThanOrEqual(3, \count($phones), 'L\'étudiant devrait avoir au moins 3 téléphones disponibles');

        // Vérifier que le téléphone de l'étudiant est présent (formaté)
        self::assertContains('01 23 45 67 89', $phones, 'Le téléphone de l\'étudiant devrait être présent');

        // Vérifier que les téléphones des parents sont présents (formatés)
        self::assertContains('01 42 34 56 78', $phones, 'Le téléphone du père devrait être présent');
        self::assertContains('01 43 45 67 89', $phones, 'Le téléphone de la mère devrait être présent');
    }

    public function testStudentWithoutFamilyHasOwnPhoneOnly(): void
    {
        // Créer un étudiant sans famille
        $child = new Person();
        $child->setForname('Orphelin')
            ->setName('Test')
            ->setGender(Person::GENDER_MALE)
            ->setPhone('0123456789')
        ;

        $student = new Student();
        $student->setPerson($child);

        // Vérifier que l'étudiant n'a que son propre téléphone
        $phones = $student->getListPhones();

        self::assertCount(1, $phones, 'L\'étudiant sans famille ne devrait avoir que son propre téléphone');
        self::assertContains('01 23 45 67 89', $phones, 'Le téléphone de l\'étudiant devrait être présent');
    }

    public function testStudentPhoneDirectAccess(): void
    {
        // Test que l'accès direct au téléphone fonctionne toujours
        $child = new Person();
        $child->setForname('Test')
            ->setName('Student')
            ->setPhone('0987654321')
        ;

        $student = new Student();
        $student->setPerson($child);

        // Test des méthodes directes
        self::assertSame('09 87 65 43 21', $student->getPhone(), 'Le téléphone direct devrait être formaté');

        // Test de la mise à jour
        $student->setPhone('0111222333');
        self::assertSame('01 11 22 23 33', $student->getPhone(), 'Le nouveau téléphone devrait être formaté');
    }
}
