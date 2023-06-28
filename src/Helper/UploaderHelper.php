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

        $img = new \Imagick($filepath);

        if ($document->isFormat('pdf')) {
            $img->setIteratorIndex(0);
        }

        // If file is image, so to compress
        if ($document->isFormat('image')) {
            $img->setCompression(\Imagick::COMPRESSION_LZW);
            $img->setCompressionQuality(80);
            $img->stripImage();
            $img->writeImage();
        }

        $pathPreview = Document::getUploadRootDir(Document::DIR_PREVIEW).'/'.$document->getFileName().'.'.Document::EXT_PNG;
        if (!is_file($pathPreview)) {
            // Genreration du preview
            $img->scaleImage(800, 0);
            $img->setImageFormat('png');
            $result['preview'] = $img->writeImage($pathPreview);
        }

        $pathThumb = Document::getUploadRootDir(Document::DIR_THUMB).'/'.$document->getFileName().'.'.Document::EXT_PNG;
        if (!is_file($pathThumb)) {
            // Generation du thumb
            $img->scaleImage(150, 0);
            $img->setImageFormat('png');
            $result['thumb'] = $img->writeImage($pathThumb);
        }

        $img->clear();

        return $result;
    }

    public function assetExists(Document $document): bool
    {
        return file_exists($document->getPath());
    }
}
