<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Entity\Document;
use App\Helper\UploaderHelper;

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
