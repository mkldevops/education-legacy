<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Document;
use App\Helper\UploaderHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    /**
     * @return array<int, TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'asset_exists',
                static fn (Document $document): bool => (new UploaderHelper())->assetExists($document)
            ),
        ];
    }
}
