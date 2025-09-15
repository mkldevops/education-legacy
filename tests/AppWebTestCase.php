<?php

declare(strict_types=1);

namespace App\Tests;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 *
 * @coversNothing
 */
class AppWebTestCase extends WebTestCase
{
    protected static KernelBrowser $client;

    protected function setUp(): void
    {
        self::createAuthenticatedClient();
    }

    public static function getUser(): User
    {
        // @phpstan-ignore-next-line
        return self::$client->getContainer()->get(Security::class)->getUser();
    }

    protected static function createAuthenticatedClient(
        string $email = UserFixtures::EMAIL
    ): void {
        self::$client = self::createClient();

        /** @var UserRepository $repository */
        $repository = self::$client->getContainer()->get(UserRepository::class);

        /** @var User $user */
        $user = $repository->findOneBy(['email' => $email]);

        self::$client->loginUser($user);
        $dispatcher = self::$client->getContainer()->get(EventDispatcherInterface::class);
        $token = self::$client->getContainer()->get(Security::class)->getToken();
    }
}
