<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Family;
use App\Entity\Student;
use App\Entity\User;
use App\Repository\FamilyRepository;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class FamilyDeletePageTest extends WebTestCase
{
    public function testFamilyDeletePageLoadsSuccessfully(): void
    {
        $client = self::createClient();
        $this->loginTestUser($client);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $schemaManager = $entityManager->getConnection()->createSchemaManager();

        if (!$schemaManager->tablesExist(['family'])) {
            self::markTestSkipped('Family table not available in the test database.');
        }

        /** @var FamilyRepository $familyRepository */
        $familyRepository = self::getContainer()->get(FamilyRepository::class);

        $family = $familyRepository->findOneBy([]);

        if (!$family instanceof Family) {
            $family = new Family();
            $family->setNumberChildren(1);
            $family->setEmail(\sprintf('family-test-%s@example.com', bin2hex(random_bytes(4))));

            $entityManager->persist($family);
            $entityManager->flush();
        }

        $client->request('GET', \sprintf('/family/delete/%d', $family->getId()));

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('h2');
        self::assertSelectorTextContains('h2', 'Supprimer famille');
    }

    public function testFamilyDeletionRemovesRelatedStudent(): void
    {
        $client = self::createClient();
        $this->loginTestUser($client);
        $container = self::getContainer();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);
        $schemaManager = $entityManager->getConnection()->createSchemaManager();

        if (!$schemaManager->tablesExist(['family', 'student', 'person'])) {
            self::markTestSkipped('Required tables are not available in the test database.');
        }

        $family = new Family();
        $family->setNumberChildren(1);
        $family->setEmail(\sprintf('family-delete-%s@example.com', bin2hex(random_bytes(4))));

        $student = new Student();
        $student->setEnable(true);

        $studentPerson = $student->getPerson();
        $studentPerson->setName('Test');
        $studentPerson->setForname('Student');
        $studentPerson->setGender('female');
        $studentPerson->setFamily($family);

        $family->addPerson($studentPerson);

        $entityManager->persist($family);
        $entityManager->persist($student);
        $entityManager->flush();

        $familyId = (int) $family->getId();
        $studentId = (int) $student->getId();

        $client->request('GET', \sprintf('/family/delete/%d', $familyId));
        self::assertResponseIsSuccessful();

        $client->submitForm('Supprimer définitivement');

        self::assertResponseRedirects('/family');
        $client->followRedirect();

        /** @var StudentRepository $studentRepository */
        $studentRepository = $container->get(StudentRepository::class);
        /** @var FamilyRepository $familyRepository */
        $familyRepository = $container->get(FamilyRepository::class);

        self::assertNull($studentRepository->find($studentId));
        self::assertNull($familyRepository->find($familyId));
    }

    private function loginTestUser(KernelBrowser $client): void
    {
        $container = self::getContainer();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);
        $schemaManager = $entityManager->getConnection()->createSchemaManager();

        if (!$schemaManager->tablesExist(['user'])) {
            self::markTestSkipped('User table not available in the test database.');
        }

        $username = 'functional_admin';

        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);

        if (!$user instanceof User) {
            $user = (new User())
                ->setUsername($username)
                ->setEmail('functional_admin@example.com')
                ->setName('Functional')
                ->setSurname('Admin')
                ->setRoles(['ROLE_ADMIN'])
            ;

            /** @var UserPasswordHasherInterface $passwordHasher */
            $passwordHasher = $container->get(UserPasswordHasherInterface::class);
            $user->setPassword($passwordHasher->hashPassword($user, 'test-password'));

            $entityManager->persist($user);
            $entityManager->flush();
        }

        $client->loginUser($user);
    }
}
