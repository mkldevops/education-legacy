<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class HomepageTest extends WebTestCase
{
    public function testHomepageRedirectsToLogin(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/');

        // Test that the homepage redirects to login for unauthenticated users
        $this->assertResponseRedirects('/login');
    }

    public function testHomepageLoadsWhenAuthenticated(): void
    {
        $client = self::createClient();

        // Create a mock user for authentication
        $userRepository = self::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneBy([]);

        if ($testUser) {
            $client->loginUser($testUser);
            $crawler = $client->request('GET', '/');

            // Test that the homepage loads successfully when authenticated
            $this->assertResponseIsSuccessful();

            // Check that we get a proper HTML response
            $this->assertSelectorExists('html');
            $this->assertSelectorExists('head');
            $this->assertSelectorExists('body');
        } else {
            self::markTestSkipped('No test user available for authentication test');
        }
    }

    public function testHomepageHasTitle(): void
    {
        $client = self::createClient();

        // Authenticate user for this test
        $userRepository = self::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneBy([]);

        if ($testUser) {
            $client->loginUser($testUser);
            $crawler = $client->request('GET', '/');

            $this->assertResponseIsSuccessful();

            // Check that the page has a title
            $this->assertSelectorExists('title');
            $title = $crawler->filter('title')->text();
            self::assertNotEmpty($title);
        } else {
            self::markTestSkipped('No test user available for title test');
        }
    }

    public function testHomepageStructure(): void
    {
        $client = self::createClient();

        // Authenticate user for this test
        $userRepository = self::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneBy([]);

        if ($testUser) {
            $client->loginUser($testUser);
            $crawler = $client->request('GET', '/');

            $this->assertResponseIsSuccessful();

            // Check basic HTML structure is present
            $this->assertSelectorExists('html[lang]');
            $this->assertSelectorExists('head meta[charset]');
            $this->assertSelectorExists('head meta[name="viewport"]');
        } else {
            self::markTestSkipped('No test user available for structure test');
        }
    }

    public function testHomepageWithAuthenticatedUser(): void
    {
        $client = self::createClient();

        // Create a mock user for authentication
        $userRepository = self::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['username' => 'admin']) ?? $userRepository->findOneBy([]);

        if ($testUser) {
            $client->loginUser($testUser);
            $crawler = $client->request('GET', '/');

            $this->assertResponseIsSuccessful();

            // When authenticated, check if period dropdown component is rendered
            // This tests our new PeriodDropdown component
            $this->assertSelectorExists('div[x-data*="open"]', 'Period dropdown component should be present when authenticated');
        } else {
            self::markTestSkipped('No test user available for authentication test');
        }
    }

    public function testPeriodDropdownComponentRendering(): void
    {
        $client = self::createClient();

        // Try to authenticate with any available user
        $userRepository = self::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneBy([]);

        if ($testUser) {
            $client->loginUser($testUser);
            $crawler = $client->request('GET', '/');

            $this->assertResponseIsSuccessful();

            // Test that our PeriodDropdown component renders without errors
            // Look for the Alpine.js data attribute that indicates our component loaded
            $dropdown = $crawler->filter('div[x-data*="open"]');

            if ($dropdown->count() > 0) {
                // Component is present, test its structure
                $this->assertSelectorExists('button[type="button"]', 'Dropdown button should be present');
                $this->assertSelectorExists('svg', 'Calendar icon should be present');

                // Test that the dropdown menu structure exists
                $this->assertSelectorExists('div[x-show="open"]', 'Dropdown menu should be present');
            }
        } else {
            self::markTestSkipped('No test user available for dropdown component test');
        }
    }
}
