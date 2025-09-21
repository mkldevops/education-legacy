<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * @internal
 *
 * @coversNothing
 */
final class RouteNotFoundTest extends WebTestCase
{
    public function testAppAdminIndexRouteExists(): void
    {
        $client = self::createClient();

        // Test that the route exists and can be generated
        $router = $client->getContainer()->get('router');

        // This should not throw a RouteNotFoundException
        try {
            $url = $router->generate('app_admin_index');
            self::assertNotEmpty($url);
        } catch (RouteNotFoundException $e) {
            self::fail('Route "app_admin_index" does not exist: '.$e->getMessage());
        }
    }

    public function testHeaderTemplateRendersWithoutError(): void
    {
        $client = self::createClient();

        // Test that any page using the header template renders without error
        $crawler = $client->request('GET', '/');

        // Check that the response is either successful or redirected (no 500 error)
        self::assertContains($client->getResponse()->getStatusCode(), [200, 302]);

        // Check that there's no Twig error in the content
        $content = $client->getResponse()->getContent();
        self::assertStringNotContainsString('Unable to generate a URL for the named route', $content);
    }
}
