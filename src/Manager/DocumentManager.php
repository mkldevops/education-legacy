<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Document;
use App\Exception\FileNotFoundException;
use App\Services\AbstractFullService;
use Imagick;
use ImagickException;
use stdClass;

class DocumentManager extends AbstractFullService
{
    public const IMAGE = 'image';
    public const PNG = 'png';
    public const PDF = 'pdf';

    private static string $pathUploads = 'uploads/documents';


    public function removesWithLinks(Document $document) : bool
    {
        $operations = $document->getOperations();

        foreach ($operations as $operation) {
            $document->removeOperation($operation);
        }

        // Remove all documents student linked
        $persons = $document->getPersons();
        foreach ($persons as $person) {
            $document->removeStudent($person);
            $person->setImage(null);
            $this->entityManager->persist($person);
        }
        $this->entityManager->flush();

        // Remove all documents account statement linked
        $accountStatements = $document->getAccountStatements();
        foreach ($accountStatements as $accountStatement) {
            $document->removeAccountStatement($accountStatement);
        }

        $this->entityManager->remove($document);
        $this->entityManager->flush();

        return true;
    }

    /**
     * @throws FileNotFoundException
     * @return array<string, mixed>
     */
    public function upload(Document $document) : array
    {
        $data = [
            'move' => false,
            'preview' => false,
            'thumb' => false,
            'errors' => false,
        ];

        if (null === $document->getFile()) {
            throw new FileNotFoundException('No file uploaded');
        }

        $document->setFileName(sha1(uniqid((string) mt_rand(), true)))
            ->setExtension($document->getFile()->guessClientExtension());

        $name = str_replace('.' . $document->getExtension(), '', $document->getFile()->getClientOriginalName());

        if (!empty($document->getPrefix())) {
            $name = $document->getPrefix() . ' ' . $name;
        }

        $document->setPath()
            ->setMime($document->getFile()->getClientMimeType())
            ->setName($name);

        $this->logger->debug(__FUNCTION__, ['document' => $document]);

        $data['move'] = $document->getFile()->move(self::getPathUploads(Document::DIR_FILE), $document->getPath());

        ['preview' => $data['preview'], 'thumb' => $data['thumb']] = $this->generateImages($document);

        if ($document->getFile()->getError() !== 0) {
            $data->errors = [
                'error' => $document->getFile()->getError(),
                'message' => $document->getFile()->getErrorMessage(),
            ];
        }

        return $data;
    }

    public static function getPathUploads(string $dir = null): string
    {
        $path = self::$pathUploads;

        if (in_array($dir, [Document::DIR_FILE, Document::DIR_THUMB, Document::DIR_PREVIEW], true)) {
            $path .= DIRECTORY_SEPARATOR . $dir;
        }

        return $path;
    }

    /**
     * @throws FileNotFoundException
     * @return array<string, bool>|array<string, string>|array<string, null>
     */
    private function generateImages(Document $document) : array
    {
        $filepath = self::getPathUploads(Document::DIR_FILE) . DIRECTORY_SEPARATOR . $document->getPath();

        // If file is not supported
        if (!is_file($filepath) || !$document->isFormat([self::PDF, self::IMAGE])) {
            throw new FileNotFoundException(sprintf('No such file %s or is not supported', $filepath));
        }

        $this->logger->debug(__FUNCTION__, ['filepath' => $filepath]);

        chmod($filepath, 0777);
        $error = false;
        $preview = null;
        $thumb = null;
        try {
            $img = new Imagick($filepath);

            if ($document->isFormat(self::PDF)) {
                $img->setIteratorIndex(0);
            }

            // If file is image, so to compress
            if ($document->isFormat(self::IMAGE)) {
                $img->setCompression(Imagick::COMPRESSION_LZW);
                $img->setCompressionQuality(80);
                $img->stripImage();
                $img->writeImage();
            }

            $filePreview = sprintf(
                "%s%s%s.%s",
                self::getPathUploads(Document::DIR_PREVIEW),
                DIRECTORY_SEPARATOR,
                $document->getFileName(),
                Document::EXT_PNG
            );
            $this->logger->debug(__FUNCTION__, ['filePreview' => $filePreview]);

            if (!is_file($filePreview)) {
                $img->scaleImage(800, 0);
                $img->setImageFormat(self::PNG);
                $preview = $img->writeImage($filePreview);
            }

            $fileThumb = sprintf(
                "%s%s%s.%s",
                self::getPathUploads(Document::DIR_THUMB),
                DIRECTORY_SEPARATOR,
                $document->getFileName(),
                Document::EXT_PNG
            );
            $this->logger->debug(__FUNCTION__, ['fileThumb' => $fileThumb]);

            if (!is_file($fileThumb)) {
                $img->scaleImage(150, 0);
                $img->setImageFormat(self::PNG);
                $thumb = $img->writeImage($fileThumb);
            }

            $img->clear();
        } catch (ImagickException $exception) {
            $error = $exception->getMessage();
        }

        return ['filepath' => $filepath, 'preview' => $preview, 'thumb' => $thumb, 'error' => $error];
    }
}
