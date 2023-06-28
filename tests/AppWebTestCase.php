<?php

declare(strict_types=1);

namespace App\Tests;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use \Symfony\Bundle\SecurityBundle\Security;

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
        $dispatcher = static::$client->getContainer()->get(EventDispatcherInterface::class);
        $token = static::$client->getContainer()->get(Security::class)->getToken();
    }

    public static function getUser(): User
    {
        /* @phpstan-ignore-next-line */
        return static::$client->getContainer()->get(Security::class)->getUser();
    }
}
