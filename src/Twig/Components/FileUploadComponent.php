<?php

declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('file_upload')]
final class FileUploadComponent
{
    public string $uploadUrl = '/api/upload';

    public string $acceptedTypes = '.pdf,.doc,.docx,.jpg,.jpeg,.png,.gif';

    public int $maxFileSize = 10;

    // MB
    public bool $multiple = false;

    public string $buttonText = 'Choisir un fichier';

    public string $buttonClass = 'inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200';

    public string $dropzoneClass = 'border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors duration-200';

    public string $progressClass = 'w-full bg-gray-200 rounded-full h-2';

    public string $successCallback = '';

    public string $errorCallback = '';

    public string $id = '';

    public function __construct()
    {
        if ('' === $this->id || '0' === $this->id) {
            $this->id = 'file-upload-'.uniqid();
        }
    }

    public function getAcceptedTypesArray(): array
    {
        return explode(',', $this->acceptedTypes);
    }

    public function getMaxFileSizeBytes(): int
    {
        return $this->maxFileSize * 1024 * 1024;
    }
}
