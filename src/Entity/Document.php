<?php

declare(strict_types=1);

namespace App\Entity;

use App\Exception\AppException;
use App\Helper\UploaderHelper;
use App\Manager\DocumentManager;
use App\Repository\DocumentRepository;
use App\Trait\AuthorEntityTrait;
use App\Trait\EnableEntityTrait;
use App\Trait\IdEntityTrait;
use App\Trait\NameEntityTrait;
use App\Trait\SchoolEntityTrait;
use App\Trait\TimestampableEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document implements \Stringable
{
    use AuthorEntityTrait;
    use EnableEntityTrait;
    use IdEntityTrait;
    use NameEntityTrait;
    use SchoolEntityTrait;
    use SoftDeleteableEntity;
    use TimestampableEntityTrait;
    /**
     * @var string
     */
    final public const DIR_FILE = 'original';

    /**
     * @var string
     */
    final public const DIR_PREVIEW = 'previews';

    /**
     * @var string
     */
    final public const DIR_THUMB = 'thumbs';

    /**
     * @var string
     */
    final public const EXT_PNG = 'png';

    #[Assert\File(maxSize: 60_000_000)]
    private ?UploadedFile $file = null;

    private ?string $fileName = null;

    private ?string $prefix = null;

    /**
     * Mime Type file.
     */
    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    #[Assert\NotBlank]
    private ?string $mime = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $path = null;

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $extension = null;

    /**
     * @var Collection<int, Person>
     */
    #[ORM\OneToMany(targetEntity: Person::class, mappedBy: 'image', cascade: ['remove'])]
    private Collection $persons;

    /**
     * @var Collection<int, Operation>
     */
    #[ORM\ManyToMany(targetEntity: Operation::class, mappedBy: 'documents', cascade: ['remove'])]
    private Collection $operations;

    /**
     * @var Collection<int, AccountStatement>
     */
    #[ORM\ManyToMany(targetEntity: AccountStatement::class, mappedBy: 'documents', cascade: ['remove'])]
    private Collection $accountStatements;

    /**
     * @var Collection<int, AccountSlip>
     */
    #[ORM\ManyToMany(targetEntity: AccountSlip::class, mappedBy: 'documents', cascade: ['remove'])]
    private Collection $accountSlips;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $size = null;

    public function __construct()
    {
        $this->accountStatements = new ArrayCollection();
        $this->operations = new ArrayCollection();
        $this->persons = new ArrayCollection();
        $this->accountSlips = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file = null): self
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @throws \ImagickException
     * @throws AppException
     */
    public function getWebPathThumb(): ?string
    {
        return $this->getWebPath(self::DIR_THUMB);
    }

    /**
     * @throws \ImagickException
     * @throws AppException
     */
    public function getWebPath(string $dir = self::DIR_FILE): ?string
    {
        $url = null;

        if (!empty($this->path)) {
            $url = DocumentManager::getPathUploads($dir).\DIRECTORY_SEPARATOR.$this->getPath($dir);
        }

        if (null !== $url && !is_file($url)) {
            $result = UploaderHelper::generateImages($this);

            if (
                (self::DIR_THUMB === $dir && empty($result['thumb']))
                || (self::DIR_PREVIEW === $dir && empty($result['preview']))
            ) {
                $url = $this->getWebPath();
            }
        }

        return $url;
    }

    public function getPath(string $dir = self::DIR_FILE): ?string
    {
        $path = $this->path;

        if (self::DIR_FILE !== $dir) {
            return self::getPathPNG($path);
        }

        return $path;
    }

    public function setPath(string $path = null): static
    {
        $this->path = null === $path && empty($this->path) ? $this->getFileName().'.'.$this->getExtension() : $path;

        return $this;
    }

    public static function getPathPNG(string $path): string
    {
        $ext = substr(strrchr($path, '.'), 1);

        return str_replace($ext, self::EXT_PNG, $path);
    }

    public function getFileName(): string
    {
        if (empty($this->fileName)) {
            $this->fileName = str_replace(strrchr((string) $this->path, '.'), '', (string) $this->path);
        }

        return $this->fileName;
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get Upload Root Dir.
     *
     * @throws AppException
     */
    public static function getUploadRootDir(string $dir = self::DIR_FILE): string
    {
        $dir = DocumentManager::getPathUploads().\DIRECTORY_SEPARATOR.self::getUploadDir($dir);

        if (!file_exists($dir) && (!mkdir($dir, 0o770, true) && !is_dir($dir))) {
            throw new AppException(sprintf('Directory "%s" was not created', $dir));
        }

        return $dir;
    }

    public static function getUploadDir(string $dir = self::DIR_FILE): string
    {
        if (!\in_array($dir, [self::DIR_FILE, self::DIR_THUMB, self::DIR_PREVIEW], true)) {
            return self::DIR_FILE;
        }

        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return $dir;
    }

    public function isFormat(array|string $formats): bool
    {
        $result = false;

        if (!\is_array($formats)) {
            $formats = [$formats];
        }

        foreach ($formats as $format) {
            $str = strpos((string) $this->getMime(), (string) $format);
            $result = (false !== $str);

            if ($result) {
                break;
            }
        }

        return $result;
    }

    public function getMime(): ?string
    {
        return $this->mime;
    }

    public function setMime(string $mime): self
    {
        $this->mime = $mime;

        return $this;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): static
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * @throws \ImagickException
     * @throws AppException
     */
    public function getWebPathPreview(): ?string
    {
        return $this->getWebPath(self::DIR_PREVIEW);
    }

    public function hasThumb(): bool
    {
        return is_file((string) $this->getAbsolutePath(self::DIR_THUMB));
    }

    public function getAbsolutePath(string $dir = self::DIR_FILE): ?string
    {
        return null === $this->path ? null : DocumentManager::getPathUploads($dir).'/'.$this->getPath($dir);
    }

    /**
     * Get is image.
     */
    public function isImage(): bool
    {
        return $this->isFormat('image');
    }

    public function setSchool(School $school): self
    {
        $this->school = $school;

        return $this;
    }

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix): static
    {
        $this->prefix = trim($prefix);

        return $this;
    }

    public function addStudent(Person $person): static
    {
        $this->persons[] = $person;

        return $this;
    }

    /**
     * Remove student.
     */
    public function removeStudent(Person $person): void
    {
        $this->persons->removeElement($person);
    }

    /**
     * @return Collection<int, Person>
     */
    public function getPersons(): Collection
    {
        return $this->persons;
    }

    public function addOperation(Operation $operation): static
    {
        $this->operations[] = $operation;

        return $this;
    }

    public function removeOperation(Operation $operation): void
    {
        $this->operations->removeElement($operation);
    }

    /**
     * @return Collection<int, Operation>
     */
    public function getOperations(): Collection
    {
        return $this->operations;
    }

    public function getFaIconFile(): string
    {
        return match ($this->mime) {
            'image/png', 'image/jpeg' => 'fa-file-image-o',
            'application/pdf' => 'fa-file-pdf-o',
            'audio/mpeg' => 'fa-file-audio-o ',
            default => 'fa-file-o',
        };
    }

    /**
     * Get information to document.
     *
     * @return array<string, int>|array<string, null>|array<string, string>
     *
     * @throws AppException
     * @throws \ImagickException
     */
    public function getInfos(): array
    {
        return [
            'path' => $this->getWebPath(),
            'pathThumb' => $this->getWebPath(self::DIR_THUMB),
            'pathPreview' => $this->getWebPath(self::DIR_PREVIEW),
            'name' => $this->getName(),
            'id' => $this->getId(),
            'title' => $this->getTitle(),
        ];
    }

    public function getTitle(): string
    {
        return 'ID : '.$this->getId().
            "\r\nNAME: ".$this->getName().
            "\r\nCREATED: ".$this->getCreatedAt()->format('d/m/Y H:i:s').
            "\r\nAUTHOR: ".$this->getAuthor()?->getName();
    }

    /**
     * Add accountStatements.
     */
    public function addAccountStatement(AccountStatement $accountStatements): static
    {
        $this->accountStatements[] = $accountStatements;

        return $this;
    }

    /**
     * Remove accountStatements.
     */
    public function removeAccountStatement(AccountStatement $accountStatements): void
    {
        $this->accountStatements->removeElement($accountStatements);
    }

    /**
     * @return Collection<int, AccountStatement>
     */
    public function getAccountStatements(): Collection
    {
        return $this->accountStatements;
    }

    public function addAccountSlip(AccountSlip $accountSlip): static
    {
        $this->accountSlips[] = $accountSlip;

        return $this;
    }

    public function removeAccountSlip(AccountSlip $accountSlip): void
    {
        $this->accountSlips->removeElement($accountSlip);
    }

    /**
     * @return Collection<int, AccountSlip>
     */
    public function getAccountSlips(): Collection
    {
        return $this->accountSlips;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;

        return $this;
    }
}
