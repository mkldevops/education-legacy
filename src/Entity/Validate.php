<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ValidateRepository;
use App\Traits\AuthorEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Fardus\Traits\Symfony\Entity\TimestampableEntityTrait;

/**
 * @ORM\Entity(repositoryClass=ValidateRepository::class)
 */
class Validate
{
    use AuthorEntityTrait;
    use IdEntityTrait;
    use TimestampableEntityTrait;

    public const TYPE_MUTED = 'muted';
    public const TYPE_PRIMARY = 'primary';
    public const TYPE_INFO = 'info';
    public const TYPE_SUCCESS = 'success';
    public const TYPE_WARNING = 'warning';
    public const TYPE_DANGER = 'danger';

    /**
     * @ORM\Column(type="string")
     */
    private ?string $message = null;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private string $type = self::TYPE_MUTED;

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return [
            'type' => $this->getType(),
            'created' => [
                'time' => $this->getCreatedAt()->getTimestamp(),
                'string' => $this->getCreatedAt()->format('d/m/Y H:i:s'),
            ],
            'author' => [
                'id' => $this->getAuthor()?->getId(),
                'name' => $this->getAuthor()?->getNameComplete(),
            ],
            'message' => $this->getMessage(),
        ];
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        if (!empty($type) && !empty(self::getListType($type))) {
            $this->type = $type;
        }

        return $this;
    }

    public static function getListType(?string $type = null): array
    {
        $list = [
            self::TYPE_MUTED => ['label' => 'muted'],
            self::TYPE_PRIMARY => ['label' => 'primary'],
            self::TYPE_INFO => ['label' => 'info'],
            self::TYPE_SUCCESS => ['label' => 'success'],
            self::TYPE_WARNING => ['label' => 'warning'],
            self::TYPE_DANGER => ['label' => 'danger'],
        ];

        if (!empty($type)) {
            if (\array_key_exists($type, $list)) {
                return $list[$type];
            }

            return [];
        }

        return $list;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
