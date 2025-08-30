<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\AuthorEntityInterface;
use App\Entity\Interface\EntityInterface;
use App\Repository\ValidateRepository;
use App\Trait\AuthorEntityTrait;
use App\Trait\IdEntityTrait;
use App\Trait\TimestampableEntityTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ValidateRepository::class)]
class Validate implements \Stringable, EntityInterface, AuthorEntityInterface
{
    use AuthorEntityTrait;
    use IdEntityTrait;
    use TimestampableEntityTrait;

    /**
     * @var string
     */
    final public const TYPE_MUTED = 'muted';

    /**
     * @var string
     */
    final public const TYPE_PRIMARY = 'primary';

    /**
     * @var string
     */
    final public const TYPE_INFO = 'info';

    /**
     * @var string
     */
    final public const TYPE_SUCCESS = 'success';

    /**
     * @var string
     */
    final public const TYPE_WARNING = 'warning';

    /**
     * @var string
     */
    final public const TYPE_DANGER = 'danger';

    #[ORM\Column(type: Types::STRING)]
    private ?string $message = null;

    #[ORM\Column(type: Types::STRING, length: 10)]
    private string $type = self::TYPE_MUTED;

    public function __toString(): string
    {
        return (string) $this->getMessage();
    }

    /**
     * @return array{type: string, created: array{time: int, string: string}, author: array{id: null|int, name: null|string}, message: null|string}
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
        if (null !== $type && '' !== $type && '0' !== $type && [] !== self::getListType($type)) {
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

        if (null !== $type && '' !== $type && '0' !== $type) {
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
