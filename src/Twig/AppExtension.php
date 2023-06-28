<?php

declare(strict_types=1);

namespace App\Twig;

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
            new TwigFunction('asset_exists', static fn(\App\Entity\Document $document): bool => (new \App\Helper\UploaderHelper())->assetExists($document)),
        ];
    }
}
