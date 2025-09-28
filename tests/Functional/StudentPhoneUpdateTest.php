<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Grade;
use App\Entity\Person;
use App\Entity\Student;
use App\Tests\AppWebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 *
 * @coversNothing
 */
final class StudentPhoneUpdateTest extends AppWebTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testStudentPhoneUpdateBug(): void
    {
        // Créer un étudiant de test
        $grade = $this->entityManager->getRepository(Grade::class)->findOneBy(['name' => 'Novice']);
        self::assertNotNull($grade, 'Grade "Novice" should exist');

        $person = new Person();
        $person->setName('Test')
            ->setForname('Student')
            ->setPhone('0123456789')
            ->setGender(Person::GENDER_MALE)
            ->setBirthday(new \DateTime('2000-01-01'))
            ->setBirthplace('Test City')
            ->setEmail('test@example.com')
        ;

        $student = new Student();
        $student->setPerson($person)
            ->setGrade($grade)
            ->setEnable(true)
        ;

        $this->entityManager->persist($student);
        $this->entityManager->flush();

        $studentId = $student->getId();
        $originalPhone = $student->getPhone();
        $newPhone = '0987654321';

        self::assertNotSame($originalPhone, $newPhone, 'Les téléphones doivent être différents pour le test');

        // Test GET de la page d'édition
        self::$client->request(Request::METHOD_GET, '/student/edit/'.$studentId);
        self::assertResponseIsSuccessful();

        // Test POST de mise à jour
        self::$client->request(Request::METHOD_PUT, '/student/update/'.$studentId, [
            'app_student' => [
                'person' => [
                    'forname' => $student->getForname(),
                    'name' => $student->getName(),
                    'phone' => $newPhone,
                    'gender' => $student->getGender(),
                    'birthday' => $student->getBirthday()?->format('Y-m-d'),
                    'birthplace' => $student->getBirthplace(),
                    'email' => $student->getEmail(),
                    'family' => null, // Pas de famille dans ce test
                ],
                'grade' => $student->getGrade()?->getId(),
                'lastSchool' => $student->getLastSchool(),
                'personAuthorized' => $student->getPersonAuthorized(),
                'remarksHealth' => $student->getRemarksHealth(),
                'letAlone' => $student->getLetAlone(),
            ],
        ]);

        // Vérifier la redirection de succès
        self::assertResponseRedirects('/student/show/'.$studentId);

        // Rafraîchir l'entité depuis la base de données
        $this->entityManager->refresh($student);

        // Vérifier que le téléphone a été mis à jour
        self::assertSame($newPhone, $student->getPhone(), 'Le numéro de téléphone devrait être mis à jour');
    }
}
