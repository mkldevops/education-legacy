<?php

declare(strict_types=1);

namespace App\Fetcher;

use App\Entity\Document;

interface DocumentFetcherInterface
{
    public function getDocument(int $id): Document;
}
