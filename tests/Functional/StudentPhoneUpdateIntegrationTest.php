<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Student;
use App\Repository\StudentRepository;
use App\Tests\AppWebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test d'intégration pour vérifier que la mise à jour du téléphone fonctionne avec les vraies fixtures.
 *
 * @internal
 *
 * @coversNothing
 */
final class StudentPhoneUpdateIntegrationTest extends AppWebTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testStudentPhoneUpdateWithRealData(): void
    {
        /** @var StudentRepository $studentRepository */
        $studentRepository = $this->entityManager->getRepository(Student::class);

        // Prendre le premier étudiant des fixtures
        $student = $studentRepository->find(1);
        self::assertNotNull($student, 'L\'étudiant ID 1 devrait exister dans les fixtures');

        $originalPhone = $student->getPhone();
        $newPhone = '0612345678';

        // S'assurer que le nouveau téléphone est différent
        self::assertNotSame($originalPhone, $newPhone, 'Les téléphones doivent être différents pour le test');

        // Test GET de la page d'édition
        self::$client->request(Request::METHOD_GET, '/student/edit/1');
        self::assertResponseIsSuccessful();

        // Test POST de mise à jour avec les vraies données du formulaire
        self::$client->request(Request::METHOD_PUT, '/student/update/1', [
            'app_student' => [
                'person' => [
                    'forname' => $student->getForname(),
                    'name' => $student->getName(),
                    'phone' => $newPhone,
                    'gender' => $student->getGender(),
                    'birthday' => $student->getBirthday()?->format('Y-m-d'),
                    'birthplace' => $student->getBirthplace(),
                    'email' => $student->getEmail(),
                    'family' => $student->getFamily()?->getId(),
                ],
                'grade' => $student->getGrade()?->getId(),
                'lastSchool' => $student->getLastSchool(),
                'personAuthorized' => $student->getPersonAuthorized(),
                'remarksHealth' => $student->getRemarksHealth(),
                'letAlone' => $student->getLetAlone(),
            ],
        ]);

        // Vérifier la redirection de succès
        self::assertResponseRedirects('/student/show/1');

        // Rafraîchir l'entité depuis la base de données
        $this->entityManager->refresh($student);

        // Le téléphone devrait être formaté automatiquement
        $expectedFormattedPhone = '06 12 34 56 78';
        self::assertSame($expectedFormattedPhone, $student->getPhone(), 'Le numéro de téléphone devrait être mis à jour et formaté');

        // Vérifier que c'est différent de l'original
        self::assertNotSame($originalPhone, $student->getPhone(), 'Le téléphone devrait avoir changé');
    }

    public function testStudentListPageDisplaysStudents(): void
    {
        // Test que la page d'index affiche les étudiants des fixtures
        self::$client->request(Request::METHOD_GET, '/student');
        self::assertResponseIsSuccessful();

        $content = self::$client->getResponse()->getContent();
        self::assertNotFalse($content);

        // Vérifier que les étudiants des fixtures sont affichés
        self::assertStringContainsString('Antoine', $content);
        self::assertStringContainsString('Dupont', $content);
        self::assertStringContainsString('Sophie', $content);
        self::assertStringContainsString('Martin', $content);
    }
}
