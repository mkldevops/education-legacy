<?php

declare(strict_types=1);

namespace App\Fetcher;

use App\Entity\Document;
use App\Exception\InvalidArgumentException;
use App\Exception\NotFoundDataException;
use App\Repository\DocumentRepository;

class DocumentFetcher implements DocumentFetcherInterface
{
    public function __construct(
        private DocumentRepository $documentRepository,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     * @throws NotFoundDataException
     */
    public function getDocument(?int $id): Document
    {
        if (null === $id) {
            throw new InvalidArgumentException('Id document is required');
        }

        $document = $this->documentRepository->find($id);

        if (!$document instanceof \App\Entity\Document) {
            throw new NotFoundDataException('Not found document with id '.$id);
        }

        return $document;
    }
}
