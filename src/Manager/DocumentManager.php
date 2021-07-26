<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: fahari
 * Date: 10/08/18
 * Time: 14:40.
 */

namespace App\Manager;

use App\Entity\Document;
use App\Exception\FileNotFoundException;
use App\Services\AbstractFullService;
use Imagick;
use ImagickException;
use stdClass;

/**
 * Description of class CourseManager.
 *
 * @author  fahari
 */
class DocumentManager extends AbstractFullService
{
    public const IMAGE = 'image';
    public const PNG = 'png';
    public const PDF = 'pdf';
    /**
     * @var string
     */
    private static $pathUploads = 'uploads/documents';

    /**
     * Creates a form to delete a Document entity by id.
     *
     * @return bool
     */
    public function removesWithLinks(Document $document)
    {
        $manager = $this->getEntityManager();

        $operations = $document->getOperations();

        foreach ($operations as $operation) {
            $document->removeOperation($operation);
        }

        // Remove all documents student linked
        $persons = $document->getPersons();
        foreach ($persons as $person) {
            $document->removeStudent($person);
            $person->setImage(null);
            $manager->persist($person);
            $manager->flush();
        }

        // Remove all documents account statement linked
        $accountStatements = $document->getAccountStatements();
        foreach ($accountStatements as $accountStatement) {
            $document->removeAccountStatement($accountStatement);
        }

        $manager->remove($document);
        $manager->flush();

        return true;
    }

    /**
     * @return array
     *
     * @throws FileNotFoundException
     */
    public function upload(Document $document)
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

        $document->setFileName(sha1(uniqid(mt_rand(), true)))
            ->setExtension($document->getFile()->guessClientExtension());

        $name = str_replace('.'.$document->getExtension(), '', $document->getFile()->getClientOriginalName());

        if (!empty($document->getPrefix())) {
            $name = $document->getPrefix().' '.$name;
        }

        $document->setPath()
            ->setMime($document->getFile()->getClientMimeType())
            ->setName($name);

        $this->logger->debug(__FUNCTION__, compact('document'));

        $data['move'] = $document->getFile()->move(self::getPathUploads(Document::DIR_FILE), $document->getPath());

        list('preview' => $data['preview'], 'thumb' => $data['thumb']) = $this->generateImages($document);

        if ($document->getFile()->getError()) {
            $data->errors = [
                'error' => $document->getFile()->getError(),
                'message' => $document->getFile()->getErrorMessage(),
            ];
        }

        unset($this->file);

        return $data;
    }

    /**
     * @return stdClass
     *
     * @throws FileNotFoundException
     */
    private function generateImages(Document $document)
    {
        $filepath = self::getPathUploads(Document::DIR_FILE).DIRECTORY_SEPARATOR.$document->getPath();

        // If file is not supported
        if (!is_file($filepath) || !$document->isFormat([self::PDF, self::IMAGE])) {
            throw new FileNotFoundException(sprintf('No such file %s or is not supported', $filepath));
        }

        $this->logger->debug(__FUNCTION__, compact('filepath'));

        chmod($filepath, 0777);
        $error = false;
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

            $filePreview = self::getPathUploads(Document::DIR_PREVIEW).DIRECTORY_SEPARATOR.$document->getFileName().'.'.Document::EXT_PNG;
            $this->logger->debug(__FUNCTION__, compact('filePreview'));

            if (!is_file($filePreview)) {
                $img->scaleImage(800, 0);
                $img->setImageFormat(self::PNG);
                $preview = $img->writeImage($filePreview);
            }

            $fileThumb = self::getPathUploads(Document::DIR_THUMB).DIRECTORY_SEPARATOR.$document->getFileName().'.'.Document::EXT_PNG;
            $this->logger->debug(__FUNCTION__, compact('fileThumb'));

            if (!is_file($fileThumb)) {
                $img->scaleImage(150, 0);
                $img->setImageFormat(self::PNG);
                $thumb = $img->writeImage($fileThumb);
            }

            $img->clear();
        } catch (ImagickException $exception) {
            $error = $exception->getMessage();
        }

        return compact('filepath', 'preview', 'thumb', 'error');
    }

    public static function getPathUploads(string $dir = null): string
    {
        $path = self::$pathUploads;

        if (in_array($dir, [Document::DIR_FILE, Document::DIR_THUMB, Document::DIR_PREVIEW], true)) {
            $path .= DIRECTORY_SEPARATOR.$dir;
        }

        return $path;
    }
}
