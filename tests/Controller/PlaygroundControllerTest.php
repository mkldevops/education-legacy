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
final class PlaygroundControllerTest extends AppWebTestCase
{
    public function testTailwindPlaygroundPageLoads(): void
    {
        self::$client->request(Request::METHOD_GET, '/playground/tw');
        self::assertResponseIsSuccessful();
    }

    public function testTailwindPlaygroundDisplaysCorrectTitle(): void
    {
        self::$client->request(Request::METHOD_GET, '/playground/tw');

        $response = self::$client->getResponse();
        self::assertResponseIsSuccessful();

        $content = $response->getContent();
        self::assertNotFalse($content);
        self::assertStringContainsString('Tailwind CSS Playground', $content);
    }

    public function testTailwindToggleOffByDefault(): void
    {
        self::$client->request(Request::METHOD_GET, '/playground/tw');

        $response = self::$client->getResponse();
        self::assertResponseIsSuccessful();

        $content = $response->getContent();
        self::assertNotFalse($content);

        // Should show "Tailwind: OFF" by default
        self::assertStringContainsString('Tailwind: OFF', $content);
        // Should not load Tailwind CDN script by default
        self::assertStringNotContainsString('cdn.tailwindcss.com', $content);
        // Should show enable button
        self::assertStringContainsString('Enable Tailwind CSS', $content);
    }

    public function testTailwindToggleOnWithQueryParam(): void
    {
        self::$client->request(Request::METHOD_GET, '/playground/tw?tw=1');

        $response = self::$client->getResponse();
        self::assertResponseIsSuccessful();

        $content = $response->getContent();
        self::assertNotFalse($content);

        // Should show "Tailwind: ON" with query parameter
        self::assertStringContainsString('Tailwind: ON', $content);
        // Should load Tailwind CDN script
        self::assertStringContainsString('cdn.tailwindcss.com', $content);
        // Should show component gallery
        self::assertStringContainsString('Buttons', $content);
        self::assertStringContainsString('Forms', $content);
        self::assertStringContainsString('Table', $content);
        self::assertStringContainsString('Alerts', $content);
        // Should configure Tailwind to disable preflight
        self::assertStringContainsString('preflight: false', $content);
    }

    public function testTailwindConfigurationIsCorrect(): void
    {
        self::$client->request(Request::METHOD_GET, '/playground/tw?tw=1');

        $response = self::$client->getResponse();
        self::assertResponseIsSuccessful();

        $content = $response->getContent();
        self::assertNotFalse($content);

        // Check Tailwind configuration
        self::assertStringContainsString('tailwind.config', $content);
        self::assertStringContainsString('preflight: false', $content);
        self::assertStringContainsString('plugins=forms,typography', $content);
        // Check custom brand colors
        self::assertStringContainsString('brand:', $content);
        self::assertStringContainsString('inspina:', $content);
    }

    public function testNoUIChangesToExistingPages(): void
    {
        // Test that enabling Tailwind on playground doesn't affect other pages
        self::$client->request(Request::METHOD_GET, '/');

        $response = self::$client->getResponse();
        self::assertResponseIsSuccessful();

        $content = $response->getContent();
        self::assertNotFalse($content);

        // Should not contain Tailwind CDN even if playground has it enabled
        self::assertStringNotContainsString('cdn.tailwindcss.com', $content);
        // Should still contain Inspina theme assets
        self::assertStringContainsString('inspina', $content);
    }
}
