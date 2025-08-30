<?php

declare(strict_types=1);

namespace App\Helper;

use App\Entity\Document;
use Twig\Extension\RuntimeExtensionInterface;

class UploaderHelper implements RuntimeExtensionInterface
{
    /**
     * @return array<string, bool>|array<string, null>
     *
     * @throws \ImagickException
     */
    public static function generateImages(Document $document): array
    {
        $filepath = Document::getUploadRootDir().'/'.$document->getPath();

        // If file is not supported
        $result = ['thumb' => null, 'preview' => null];
        if (!is_file($filepath)) {
            return $result;
        }

        if (!$document->isFormat(['pdf', 'image'])) {
            return $result;
        }

        $imagick = new \Imagick($filepath);

        if ($document->isFormat('pdf')) {
            $imagick->setIteratorIndex(0);
        }

        // If file is image, so to compress
        if ($document->isFormat('image')) {
            $imagick->setCompression(\Imagick::COMPRESSION_LZW);
            $imagick->setCompressionQuality(80);
            $imagick->stripImage();
            $imagick->writeImage();
        }

        $pathPreview = Document::getUploadRootDir(Document::DIR_PREVIEW).'/'.$document->getFileName().'.'.Document::EXT_PNG;
        if (!is_file($pathPreview)) {
            // Genreration du preview
            $imagick->scaleImage(800, 0);
            $imagick->setImageFormat('png');
            $result['preview'] = $imagick->writeImage($pathPreview);
        }

        $pathThumb = Document::getUploadRootDir(Document::DIR_THUMB).'/'.$document->getFileName().'.'.Document::EXT_PNG;
        if (!is_file($pathThumb)) {
            // Generation du thumb
            $imagick->scaleImage(150, 0);
            $imagick->setImageFormat('png');
            $result['thumb'] = $imagick->writeImage($pathThumb);
        }

        $imagick->clear();

        return $result;
    }

    public function assetExists(Document $document): bool
    {
        return file_exists($document->getPath());
    }
}
