<?php

declare(strict_types=1);

namespace App\Services;

use App\Exception\AppException;
use Exception;
use Google_Service_Drive;
use Google_Service_Drive_FileList;

class GoogleDriveService extends GoogleService
{
    /**
     * @throws Exception
     */
    public function uploadFile(string $path): void
    {
        $drive = new Google_Service_Drive($this->getClient());
        $files = $drive->files->listFiles();

        dump($files, $path);
    }

    /**
     * @throws AppException
     *
     * @return mixed[]
     */
    public function getListFiles(array $params): array
    {
        $files = [];
        try {
            $drive = new Google_Service_Drive($this->getClient());
            $googleListFiles = $drive->files->listFiles($params);

            if (!$googleListFiles instanceof Google_Service_Drive_FileList) {
                $msg = ' Error on get files to google drive';
                $this->logger->error(__METHOD__.$msg, ['files' => $files, 'params' => $params]);
                throw new AppException($msg);
            }
        } catch (Exception $e) {
            $this->logger->error(__METHOD__.' '.$e->getMessage(), ['params' => $params]);
            throw new AppException($e->getMessage(), $e->getCode(), $e);
        }

        return $files;
    }
}
