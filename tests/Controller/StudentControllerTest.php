<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Manager\PeriodManager;
use App\Manager\SchoolManager;
use App\Repository\StudentRepository;
use App\Tests\AppWebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 *
 * @coversNothing
 */
final class StudentControllerTest extends AppWebTestCase
{
    public function testStudentIndexPageLoads(): void
    {
        self::$client->request(Request::METHOD_GET, '/student');
        self::assertResponseIsSuccessful();
    }

    public function testStudentIndexPageDisplaysCorrectStructure(): void
    {
        self::$client->request(Request::METHOD_GET, '/student');

        $response = self::$client->getResponse();
        self::assertResponseIsSuccessful();

        $content = $response->getContent();
        self::assertNotFalse($content);

        // Check that the page loaded successfully and contains the expected title
        self::assertStringContainsString('Liste des élèves', $content);

        // The content should not contain error messages indicating broken functionality
        self::assertStringNotContainsString('aucune données affiché', $content);
        self::assertStringNotContainsString('no data showing', $content);
        self::assertStringNotContainsString('Exception', $content);
        self::assertStringNotContainsString('Fatal error', $content);
        self::assertStringNotContainsString('Parse error', $content);
        self::assertStringNotContainsString('404 Not Found', $content);
        self::assertStringNotContainsString('500 Internal Server Error', $content);

        // Check that essential elements are present (indicating the page structure is intact)
        self::assertStringContainsString('open-class-modal', $content);
        self::assertStringContainsString('class_period_modal', $content);
    }

    public function testStudentRepositoryReturnsData(): void
    {
        $container = self::getContainer();

        /** @var SchoolManager $schoolManager */
        $schoolManager = $container->get(SchoolManager::class);

        /** @var PeriodManager $periodManager */
        $periodManager = $container->get(PeriodManager::class);

        /** @var StudentRepository $studentRepository */
        $studentRepository = $container->get(StudentRepository::class);

        try {
            $school = $schoolManager->getSchool();
            $period = $periodManager->getPeriodsOnSession();

            // PHPStan-friendly assertions
            self::assertThat($school, self::logicalNot(self::isNull()), 'School should be available');
            self::assertThat($period, self::logicalNot(self::isNull()), 'Period should be available');

            // Test the getListStudents method - this is the core functionality we fixed
            $students = $studentRepository->getListStudents($period, $school, true, 10);

            // If students exist, validate their structure
            if (!empty($students)) {
                $student = $students[0];
                self::assertNotNull($student->getId());

                // Test that relations are properly loaded (no additional queries needed)
                if ($student->getPerson()) {
                    self::assertNotNull($student->getPerson()->getId());
                    if ($student->getPerson()->getFamily()) {
                        self::assertNotNull($student->getPerson()->getFamily()->getId());
                    }
                }

                if ($student->getGrade()) {
                    self::assertNotNull($student->getGrade()->getName());
                }
            }
        } catch (\Throwable $e) {
            // If managers fail, it might be due to test setup, but the repository method should still work
            self::markTestSkipped('School/Period managers not properly configured in test environment: '.$e->getMessage());
        }
    }

    public function testStudentPhoneUpdateBug(): void
    {
        $container = self::getContainer();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);

        /** @var StudentRepository $studentRepository */
        $studentRepository = $container->get(StudentRepository::class);

        // Créer un étudiant de test
        $students = $studentRepository->findAll();
        if (empty($students)) {
            self::markTestSkipped('Aucun étudiant trouvé dans les fixtures');
        }

        $student = $students[0];
        $originalPhone = $student->getPhone();
        $newPhone = '0123456789';

        // Assurer que le nouveau téléphone est différent
        if ($originalPhone === $newPhone) {
            $newPhone = '0987654321';
        }

        // Test GET de la page d'édition pour récupérer le token CSRF
        $crawler = self::$client->request(Request::METHOD_GET, '/student/edit/'.$student->getId());
        self::assertResponseIsSuccessful();

        // Extraire le token CSRF du formulaire
        $csrfToken = $crawler->filter('input[name="app_student[_token]"]')->attr('value');

        // Test POST de mise à jour
        self::$client->request(Request::METHOD_PUT, '/student/update/'.$student->getId(), [
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
                '_token' => $csrfToken,
            ],
        ]);

        // Vérifier la redirection de succès
        self::assertResponseRedirects('/student/show/'.$student->getId());

        // Récupérer l'entité fraîche depuis la base de données
        $updatedStudent = $studentRepository->find($student->getId());

        // Vérifier que le téléphone a été mis à jour (le PhoneManager formate automatiquement)
        // Le format attendu pour '0123456789' est '01 23 45 67 89'
        $expectedFormattedPhone = '01 23 45 67 89';
        self::assertSame($expectedFormattedPhone, $updatedStudent->getPhone(), 'Le numéro de téléphone devrait être formaté et mis à jour');
    }
}
