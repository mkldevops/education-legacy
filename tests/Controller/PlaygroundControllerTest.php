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
        // Note: Tailwind is now loaded globally in base.html.twig so CDN will be present
        // Should show enable button for playground-specific features
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

    public function testTailwindIsNowGloballyEnabled(): void
    {
        // Test that Tailwind is now globally enabled in the new base template
        self::$client->request(Request::METHOD_GET, '/');

        $response = self::$client->getResponse();
        self::assertResponseIsSuccessful();

        $content = $response->getContent();
        self::assertNotFalse($content);

        // Should contain Tailwind CDN since it's now the main CSS framework
        self::assertStringContainsString('cdn.tailwindcss.com', $content);
        // Should contain modern styling classes
        self::assertStringContainsString('bg-gray-50', $content);
        // Confirm the new modern HTML structure
        self::assertStringContainsString('class="h-full bg-gray-50"', $content);
    }
}
