<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Document;
use App\Exception\FileNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class DocumentManager
{
    /**
     * @var string
     */
    public const IMAGE = 'image';

    /**
     * @var string
     */
    public const PNG = 'png';

    /**
     * @var string
     */
    public const PDF = 'pdf';

    private static string $pathUploads = 'uploads/documents';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {}

    public function removesWithLinks(Document $document): bool
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
     * @return array{move: File, preview: null|bool|string, thumb: null|bool|string, errors: array<string, int|string>|false}
     *
     * @throws FileNotFoundException
     * @throws \Exception
     */
    public function upload(Document $document): array
    {
        $data = [
            'move' => false,
            'preview' => false,
            'thumb' => false,
            'errors' => false,
        ];

        if (!$document->getFile() instanceof UploadedFile) {
            throw new FileNotFoundException('No file uploaded');
        }

        $document->setFileName(sha1(uniqid((string) random_int(0, mt_getrandmax()), true)))
            ->setExtension($document->getFile()->guessClientExtension())
        ;

        $name = str_replace('.'.$document->getExtension(), '', $document->getFile()->getClientOriginalName());

        if (!\in_array($document->getPrefix(), [null, '', '0'], true)) {
            $name = $document->getPrefix().' '.$name;
        }

        $document->setPath()
            ->setMime($document->getFile()->getClientMimeType())
            ->setName($name)
        ;

        $this->logger->debug(__FUNCTION__, ['document' => $document]);

        $data['move'] = $document->getFile()->move(self::getPathUploads(Document::DIR_FILE), $document->getPath());

        ['preview' => $data['preview'], 'thumb' => $data['thumb']] = $this->generateImages($document);

        if (0 !== $document->getFile()->getError()) {
            $data['errors'] = [
                'error' => $document->getFile()->getError(),
                'message' => $document->getFile()->getErrorMessage(),
            ];
        }

        return $data;
    }

    public static function getPathUploads(?string $dir = null): string
    {
        $path = self::$pathUploads;

        if (\in_array($dir, [Document::DIR_FILE, Document::DIR_THUMB, Document::DIR_PREVIEW], true)) {
            $path .= \DIRECTORY_SEPARATOR.$dir;
        }

        return $path;
    }

    /**
     * @return array<string, bool>|array<string, null>|array<string, string>
     *
     * @throws FileNotFoundException
     */
    private function generateImages(Document $document): array
    {
        $filepath = self::getPathUploads(Document::DIR_FILE).\DIRECTORY_SEPARATOR.$document->getPath();

        // If file is not supported
        if (!is_file($filepath) || !$document->isFormat([self::PDF, self::IMAGE])) {
            throw new FileNotFoundException(\sprintf('No such file %s or is not supported', $filepath));
        }

        $this->logger->debug(__FUNCTION__, ['filepath' => $filepath]);

        chmod($filepath, 0o777);
        $error = false;
        $preview = null;
        $thumb = null;

        try {
            $imagick = new \Imagick($filepath);

            if ($document->isFormat(self::PDF)) {
                $imagick->setIteratorIndex(0);
            }

            // If file is image, so to compress
            if ($document->isFormat(self::IMAGE)) {
                $imagick->setCompression(\Imagick::COMPRESSION_LZW);
                $imagick->setCompressionQuality(80);
                $imagick->stripImage();
                $imagick->writeImage();
            }

            $filePreview = \sprintf(
                '%s%s%s.%s',
                self::getPathUploads(Document::DIR_PREVIEW),
                \DIRECTORY_SEPARATOR,
                $document->getFileName(),
                Document::EXT_PNG
            );
            $this->logger->debug(__FUNCTION__, ['filePreview' => $filePreview]);

            if (!is_file($filePreview)) {
                $imagick->scaleImage(800, 0);
                $imagick->setImageFormat(self::PNG);
                $preview = $imagick->writeImage($filePreview);
            }

            $fileThumb = \sprintf(
                '%s%s%s.%s',
                self::getPathUploads(Document::DIR_THUMB),
                \DIRECTORY_SEPARATOR,
                $document->getFileName(),
                Document::EXT_PNG
            );
            $this->logger->debug(__FUNCTION__, ['fileThumb' => $fileThumb]);

            if (!is_file($fileThumb)) {
                $imagick->scaleImage(150, 0);
                $imagick->setImageFormat(self::PNG);
                $thumb = $imagick->writeImage($fileThumb);
            }

            $imagick->clear();
        } catch (\ImagickException $imagickException) {
            $error = $imagickException->getMessage();
        }

        return ['filepath' => $filepath, 'preview' => $preview, 'thumb' => $thumb, 'error' => $error];
    }
}
