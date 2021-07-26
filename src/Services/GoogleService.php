<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: fahari
 * Date: 10/08/18
 * Time: 10:31.
 */

namespace App\Services;

use App\Exception\AppException;
use Google_Client;
use Google_Exception;
use Google_Service_Calendar;
use Google_Service_Drive;

/**
 * Description of class GoogleCalendarManager.
 *
 * @author  fahari
 */
class GoogleService extends AbstractService
{
    protected Google_Client $client;

    protected string $pathCredentiels;

    protected string $pathTokens;

    protected string $authCode;

    /**
     * Returns an authorized API client.
     *
     * @return Google_Client the authorized client object
     *
     * @throws \Exception
     */
    public function getClient(): Google_Client
    {
        // Load previously authorized credentials from a file.
        $credentialsPath = $this->pathTokens.DIRECTORY_SEPARATOR.'token.json';
        $accessToken = $this->getAccessToken($credentialsPath);
        $this->client->setAccessToken($accessToken);

        // Refresh the token if it's expired.
        if ($this->client->isAccessTokenExpired()) {
            $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
            file_put_contents($credentialsPath, json_encode($this->client->getAccessToken()));
        }

        return $this->client;
    }

    /**
     * @required
     *
     * @throws Google_Exception
     */
    public function setClient(Google_Client $client): GoogleService
    {
        $this->client = $client;
        $this->init();

        return $this;
    }

    /**
     * getAccessToken.
     *
     * @return string[]
     *
     * @throws AppException
     */
    public function getAccessToken(string $credentialsPath): array
    {
        if (file_exists($credentialsPath)) {
            $accessToken = json_decode((string) file_get_contents($credentialsPath), true);
        } else {
            // Request authorization from the user.
            $authUrl = $this->client->createAuthUrl();

            // Exchange authorization code for an access token.
            $accessToken = $this->client->fetchAccessTokenWithAuthCode($this->authCode);
            $this->logger->info(__METHOD__, ['credentialsPath' => $credentialsPath, 'authUrl' => $authUrl, 'accessToken' => $accessToken]);

            // Check to see if there was an error.
            if (empty($accessToken) || array_key_exists('error', $accessToken)) {
                $msg = sprintf("Obtain your authCode with :\n \"%s\"", $authUrl);
                throw new AppException(sprintf("%s  \n %s", $msg, json_encode(['accessToken' => $accessToken])));
            }

            // Store the credentials to disk.
            if (!file_exists(dirname($credentialsPath))) {
                mkdir(dirname($credentialsPath), 0700, true);
            }

            file_put_contents($credentialsPath, json_encode($accessToken));
            $this->logger->info('Credentials saved to '.$credentialsPath);
        }

        return $accessToken;
    }

    public function init(): void
    {
        $this->client->setApplicationName('Google API PHP Education');
        $this->client->setScopes([
            Google_Service_Calendar::CALENDAR,
            Google_Service_Drive::DRIVE,
            \Google_Service_Script::WWW_GOOGLE_COM_M8_FEEDS,
        ]);

        $this->client->setAccessType('offline');
    }

    /**
     * @return string
     */
    public function getPathCredentiels(): ?string
    {
        return $this->pathCredentiels;
    }

    public function setPathCredentiels(string $pathCredentiels): self
    {
        $this->pathCredentiels = $pathCredentiels;

        return $this;
    }

    /**
     * @return string
     */
    public function getPathTokens(): ?string
    {
        return $this->pathTokens;
    }

    public function setPathTokens(string $pathTokens): self
    {
        $this->pathTokens = $pathTokens;
        if (!file_exists($this->pathTokens)) {
            mkdir($this->pathTokens, 0775, true);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthCode(): ?string
    {
        return $this->authCode;
    }

    public function setAuthCode(string $authCode): self
    {
        $this->authCode = $authCode;

        return $this;
    }

    public function setRedirectUri(string $redirectUri): GoogleService
    {
        $this->client->setRedirectUri($redirectUri);

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthUrl(): ?string
    {
        return $this->client->createAuthUrl();
    }
}
