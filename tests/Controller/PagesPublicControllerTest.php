<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 *
 * @coversNothing
 */
final class PagesPublicControllerTest extends WebTestCase
{
    /**
     * @dataProvider providePagesCases
     */
    public static function testPages(string $url): void
    {
        $client = self::createClient();

        $client->request(Request::METHOD_GET, $url);
        self::assertResponseIsSuccessful();
    }

    /**
     * @return iterable<array-key, string[]>
     */
    public static function providePagesCases(): iterable
    {
        yield ['/login'];
    }
}
