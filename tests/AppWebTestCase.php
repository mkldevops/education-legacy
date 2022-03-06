<?php

declare(strict_types=1);

namespace App\Tests;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class AppWebTestCase extends WebTestCase
{
    protected static KernelBrowser $client;

    protected function setUp(): void
    {
        static::createAuthenticatedClient();
    }

    protected static function createAuthenticatedClient(
        string $email = UserFixtures::EMAIL
    ): void {
        static::$client = static::createClient();

        /** @var UserRepository $repository */
        $repository = static::$client->getContainer()->get(UserRepository::class);

        /** @var User $user */
        $user = $repository->findOneBy(['email' => $email]);

        static::$client->loginUser($user);
    }

    public static function queryRequest(string $query): string
    {
        static::$client->request(Request::METHOD_GET, '/', ['query' => $query]);
        static::assertResponseIsSuccessful();

        $content = (string) static::$client->getResponse()->getContent();
        static::assertJson($content);

        return $content;
    }

    public static function getUser(): User
    {
        /* @phpstan-ignore-next-line */
        return static::$client->getContainer()->get(Security::class)->getUser();
    }
}
