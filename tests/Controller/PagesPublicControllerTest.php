<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\AppWebTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class PagesPublicControllerTest extends WebTestCase
{
    /**
     * @dataProvider providers
     */
    public static function testPages(string $url): void
    {
        $client = static::createClient();

        $client->request(Request::METHOD_GET, $url);
        static::assertResponseIsSuccessful();
    }

    public static function providers(): iterable
    {
        yield ['/login'];
    }
}
