<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Person;
use App\Entity\Student;
use PHPUnit\Framework\TestCase;

/**
 * Test unitaire pour vérifier le comportement du téléphone sur l'entité Student.
 *
 * @internal
 *
 * @coversNothing
 */
final class StudentPhoneTest extends TestCase
{
    public function testStudentPhoneGetterSetter(): void
    {
        $person = new Person();
        $student = new Student();
        $student->setPerson($person);

        $phone = '0123456789';
        $student->setPhone($phone);

        // Le téléphone est automatiquement formaté par PhoneManager
        $expectedFormattedPhone = '01 23 45 67 89';
        self::assertSame($expectedFormattedPhone, $student->getPhone(), 'Le téléphone devrait être formaté');
        self::assertSame($expectedFormattedPhone, $person->getPhone(), 'Le téléphone devrait être défini dans l\'entité Person associée');
    }

    public function testPersonPhoneStringToArray(): void
    {
        $person = new Person();

        // Test avec un numéro simple
        $person->setPhone('0123456789');
        self::assertSame('01 23 45 67 89', $person->getPhone());

        // Test avec plusieurs numéros
        $person->setPhone('0123456789;0987654321');
        $phones = $person->getListPhones();

        self::assertCount(2, $phones, 'Devrait avoir 2 numéros de téléphone');
        self::assertContains('01 23 45 67 89', $phones, 'Devrait contenir le premier numéro formaté');
        self::assertContains('09 87 65 43 21', $phones, 'Devrait contenir le deuxième numéro formaté');
    }

    public function testPersonPhoneUpdate(): void
    {
        $person = new Person();

        // Définir un téléphone initial
        $person->setPhone('0123456789');
        $originalPhone = $person->getPhone(); // '01 23 45 67 89'

        // Mettre à jour le téléphone
        $newPhone = '0987654321';
        $person->setPhone($newPhone);
        $expectedFormattedNewPhone = '09 87 65 43 21';

        self::assertNotSame($originalPhone, $person->getPhone(), 'Le téléphone devrait être différent après mise à jour');
        self::assertSame($expectedFormattedNewPhone, $person->getPhone(), 'Le nouveau téléphone devrait être correctement formaté');
    }
}
