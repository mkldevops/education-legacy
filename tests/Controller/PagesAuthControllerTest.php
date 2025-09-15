<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\AppWebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 *
 * @coversNothing
 */
final class PagesAuthControllerTest extends AppWebTestCase
{
    /**
     * @dataProvider providePagesWithAuthCases
     */
    public static function testPagesWithAuth(string $url): void
    {
        self::$client->request(Request::METHOD_GET, $url);
        self::assertResponseIsSuccessful();
    }

    /**
     * @return iterable<array-key, string[]>
     */
    public static function providePagesWithAuthCases(): iterable
    {
        yield ['/'];
        yield ['/account'];
        yield ['/class-school'];
        yield ['/student'];
    }
}
