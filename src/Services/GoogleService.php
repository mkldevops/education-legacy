<?php

declare(strict_types=1);

namespace App\Services;

use App\Exception\AppException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class GoogleService
{
    protected string $authCode;

    public function __construct(
        public readonly LoggerInterface $logger,
        private readonly \Google_Client $client,
        #[Autowire('%kernel.project_dir%/var/tokens')]
        private readonly string $pathTokens,
    ) {
        if (!file_exists($this->pathTokens)) {
            mkdir($this->pathTokens, 0o775, true);
        }
    }

    /**
     * Returns an authorized API client.
     *
     * @return \Google_Client the authorized client object
     *
     * @throws \Exception
     */
    public function getClient(): \Google_Client
    {
        // Load previously authorized credentials from a file.
        $credentialsPath = $this->pathTokens.\DIRECTORY_SEPARATOR.'token.json';
        $accessToken = $this->getAccessToken($credentialsPath);
        $this->client->setAccessToken($accessToken);

        // Refresh the token if it's expired.
        if ($this->client->isAccessTokenExpired()) {
            $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
            file_put_contents($credentialsPath, json_encode($this->client->getAccessToken(), JSON_THROW_ON_ERROR));
        }

        return $this->client;
    }

    /**
     * getAccessToken.
     *
     * @return string[]
     *
     * @throws AppException
     * @throws \JsonException
     */
    public function getAccessToken(string $credentialsPath): array
    {
        if (file_exists($credentialsPath)) {
            $accessToken = json_decode((string) file_get_contents($credentialsPath), true, 512, JSON_THROW_ON_ERROR);
        } else {
            // Request authorization from the user.
            $authUrl = $this->client->createAuthUrl();

            // Exchange authorization code for an access token.
            $accessToken = $this->client->fetchAccessTokenWithAuthCode($this->authCode);
            $this->logger->info(__METHOD__, ['credentialsPath' => $credentialsPath, 'authUrl' => $authUrl, 'accessToken' => $accessToken]);

            // Check to see if there was an error.
            if ([] === $accessToken || \array_key_exists('error', $accessToken)) {
                $msg = sprintf("Obtain your authCode with :\n \"%s\"", $authUrl);

                throw new AppException(sprintf("%s  \n %s", $msg, json_encode(['accessToken' => $accessToken], JSON_THROW_ON_ERROR)));
            }

            // Store the credentials to disk.
            if (!file_exists(\dirname($credentialsPath))) {
                mkdir(\dirname($credentialsPath), 0o700, true);
            }

            file_put_contents($credentialsPath, json_encode($accessToken, JSON_THROW_ON_ERROR));
            $this->logger->info('Credentials saved to '.$credentialsPath);
        }

        return $accessToken;
    }

    public function init(): void
    {
        $this->client->setApplicationName('Google API PHP Education');
        $this->client->setScopes([
            \Google_Service_Calendar::CALENDAR,
            \Google_Service_Drive::DRIVE,
            \Google_Service_Script::WWW_GOOGLE_COM_M8_FEEDS,
        ]);

        $this->client->setAccessType('offline');
    }

    public function getAuthCode(): string
    {
        return $this->authCode;
    }

    public function setAuthCode(string $authCode): self
    {
        $this->authCode = $authCode;

        return $this;
    }

    public function setRedirectUri(string $redirectUri): static
    {
        $this->client->setRedirectUri($redirectUri);

        return $this;
    }

    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }
}
