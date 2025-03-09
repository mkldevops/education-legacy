<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\AppWebTestCase;
use Symfony\Component\HttpFoundation\Request;

class PagesAuthControllerTest extends AppWebTestCase
{
    /**
     * @dataProvider providers
     */
    public static function testPagesWithAuth(string $url): void
    {
        static::$client->request(Request::METHOD_GET, $url);
        static::assertResponseIsSuccessful();
    }

    /**
     * @return iterable<array-key, string[]>
     */
    public static function providers(): iterable
    {
        yield ['/'];
        yield ['/account'];
        yield ['/class-school'];
        yield ['/student'];
    }
}
