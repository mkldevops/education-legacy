<?php

declare(strict_types=1);

namespace App\Services;

use App\Exception\AppException;
use DateTime;
use DateTimeInterface;
use Exception;
use Google_Service_Calendar;

class GoogleCalendarService extends GoogleService
{
    protected ?DateTimeInterface $timeMin;

    protected ?DateTimeInterface $timeMax;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->timeMin = new DateTime();
    }

    /**
     * @return mixed[]
     *
     * @throws AppException
     */
    public function getEvents(?string $query = null, int $maxResults = 1000): array
    {
        try {
            $service = new Google_Service_Calendar($this->getClient());
        } catch (Exception $exception) {
            throw new AppException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        // Print the next 10 events on the user's calendar.
        $calendarId = 'primary';

        $optParams = [
            'maxResults' => $maxResults,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => $this->timeMin?->format('c'),
        ];

        if (null !== $this->timeMax) {
            $optParams['timeMax'] = $this->timeMax->format('c');
        }

        if (!empty($query)) {
            $optParams['q'] = $query;
        }

        $results = $service->events->listEvents($calendarId, $optParams);

        return $results->getItems();
    }

    public function setTimeMin(DateTimeInterface $timeMin): self
    {
        $this->timeMin = $timeMin;

        return $this;
    }

    public function setTimeMax(DateTimeInterface $timeMax): self
    {
        $this->timeMax = $timeMax;

        return $this;
    }
}
