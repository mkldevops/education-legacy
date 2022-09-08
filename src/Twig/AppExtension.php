<?php

declare(strict_types=1);

namespace App\Twig;

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
            new TwigFunction('asset_exists', [UploaderHelper::class, 'assetExists']),
        ];
    }
}
