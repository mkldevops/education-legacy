<?php

declare(strict_types=1);

namespace App\Entity;

use App\Exception\AppException;
use App\Manager\DocumentManager;
use App\Traits\AuthorEntityTrait;
use App\Traits\SchoolEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Fardus\Traits\Symfony\Entity\NameEntityTrait;
use Fardus\Traits\Symfony\Entity\TimestampableEntityTrait;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Imagick;
use ImagickException;
use RuntimeException;
use stdClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\DocumentRepository;

/**
 * @ORM\Entity(repositoryClass=DocumentRepository::class)
 */
class Document
{
    public const DIR_FILE = 'original';
    public const DIR_PREVIEW = 'previews';
    public const DIR_THUMB = 'thumbs';
    public const EXT_PNG = 'png';

    use IdEntityTrait;
    use NameEntityTrait;
    use AuthorEntityTrait;
    use EnableEntityTrait;
    use TimestampableEntityTrait;
    use SoftDeleteableEntity;
    use SchoolEntityTrait;

    /**
     * @Assert\File(maxSize="60000000")
     */
    private ?UploadedFile $file = null;

    private ?string $fileName = null;

    private ?string $prefix = null;

    /**
     * Mime Type file.
     *
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Assert\NotBlank
     */
    private ?string $mime = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $path = null;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private ?string $extension = null;

    /**
     * @var Collection|Person[]
     * @ORM\OneToMany(targetEntity=Person::class, mappedBy="image", cascade={"remove"})
     */
    private Collection|array $persons;

    /**
     * @var Collection|Operation[]
     * @ORM\ManyToMany(targetEntity=Operation::class, mappedBy="documents", cascade={"remove"})
     */
    private Collection|array $operations;

    /**
     * @var AccountStatement[]|Collection
     * @ORM\ManyToMany(targetEntity=AccountStatement::class, mappedBy="documents", cascade={"remove"})
     */
    private Collection|array $accountStatements;

    /**
     * @var Collection|AccountSlip[]
     * @ORM\ManyToMany(targetEntity=AccountSlip::class, mappedBy="documents", cascade={"remove"})
     */
    private Collection|array $accountSlips;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
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
     * @throws ImagickException
     * @throws AppException
     */
    public function getWebPathThumb(): ?string
    {
        return $this->getWebPath(self::DIR_THUMB);
    }

    /**
     * @throws ImagickException
     * @throws AppException
     */
    public function getWebPath(string $dir = self::DIR_FILE): ?string
    {
        $url = null;

        if (!empty($this->path)) {
            $url = DocumentManager::getPathUploads($dir) . DIRECTORY_SEPARATOR . $this->getPath($dir);
        }

        if ($url !== null && !is_file($url)) {
            $result = $this->generateImages();

            if ((self::DIR_THUMB === $dir && empty($result['thumb']))
                || (self::DIR_PREVIEW === $dir && empty($result['preview']))
            ) {
                $url = $this->getWebPath();
            }
        }

        return $url;
    }


    public function getPath(string $dir = self::DIR_FILE) : ?string
    {
        $path = $this->path;

        if (self::DIR_FILE !== $dir) {
            $path = self::getPathPNG($path);
        }

        return $path;
    }


    public function setPath(string $path = null) : static
    {
        $this->path = null === $path && empty($this->path) ? $this->getFileName() . '.' . $this->getExtension() : $path;

        return $this;
    }


    public static function getPathPNG(string $path) : string
    {
        $ext = substr(strrchr($path, '.'), 1);

        return (string) str_replace($ext, self::EXT_PNG, $path);
    }

    /**
     * @throws ImagickException
     * @throws AppException
     */
    public function generateImages() : array
    {
        if (empty($this->fileName)) {
            $this->fileName = $this->getFileName();
        }


        $filepath = self::getUploadRootDir() . '/' . $this->path;

        // If file is not supported
        if (!is_file($filepath) || !$this->isFormat(['pdf', 'image'])) {
            throw new AppException('File '.$filepath.' not supported');
        }

        $img = new Imagick($filepath);

        if ($this->isFormat('pdf')) {
            $img->setIteratorIndex(0);
        }

        // If file is image, so to compress
        if ($this->isFormat('image')) {
            $img->setCompression(Imagick::COMPRESSION_LZW);
            $img->setCompressionQuality(80);
            $img->stripImage();
            $img->writeImage();
        }


        $result = ['thumb' => null, 'preview' => null];
        $pathPreview = self::getUploadRootDir(self::DIR_PREVIEW) . '/' . $this->fileName . '.' . self::EXT_PNG;
        if (!is_file($pathPreview)) {
            // Genreration du preview
            $img->scaleImage(800, 0);
            $img->setImageFormat('png');
            $result['preview'] = $img->writeImage($pathPreview);
        }

        $pathThumb = self::getUploadRootDir(self::DIR_THUMB) . '/' . $this->fileName . '.' . self::EXT_PNG;
        if (!is_file($pathThumb)) {
            // Generation du thumb
            $img->scaleImage(150, 0);
            $img->setImageFormat('png');
            $result['thumb'] = $img->writeImage($pathThumb);
        }

        $img->clear();

        return $result;
    }


    public function getFileName(): ?string
    {
        if (empty($this->fileName)) {
            $this->fileName = str_replace(strrchr((string) $this->path, '.'), '', (string) $this->path);
        }

        return $this->fileName;
    }

    public function setFileName(string $fileName): Document
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get Upload Root Dir.
     *
     * @param string $dir
     */
    public static function getUploadRootDir($dir = self::DIR_FILE): string
    {
        $dir = DocumentManager::getPathUploads() . DIRECTORY_SEPARATOR . self::getUploadDir($dir);
        //dd($dir);
        if (!file_exists($dir) && (!mkdir($dir, 0770, true) && !is_dir($dir))) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }

        return $dir;
    }

    public static function getUploadDir(string $dir = self::DIR_FILE): string
    {
        if (!in_array($dir, [self::DIR_FILE, self::DIR_THUMB, self::DIR_PREVIEW], true)) {
            $dir = self::DIR_FILE;
        }

        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return $dir;
    }


    public function isFormat(array|string $formats) : bool
    {
        $result = false;

        if (!is_array($formats)) {
            $formats = [$formats];
        }

        foreach ($formats as $format) {
            $str = strpos((string) $this->getMime(), $format);
            $result = (false !== $str);

            if ($result) {
                break;
            }
        }

        return $result;
    }


    public function getMime() : ?string
    {
        return $this->mime;
    }

    public function setMime(string $mime): self
    {
        $this->mime = $mime;

        return $this;
    }


    public function getExtension() : ?string
    {
        return $this->extension;
    }

    public function setExtension(string $extension) : static
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * @throws ImagickException
     * @throws AppException
     */
    public function getWebPathPreview() : ?string
    {
        return $this->getWebPath(self::DIR_PREVIEW);
    }

    public function hasThumb(): bool
    {
        return is_file((string) $this->getAbsolutePath(self::DIR_THUMB));
    }

    public function getAbsolutePath($dir = self::DIR_FILE): ?string
    {
        return null === $this->path ? null : DocumentManager::getPathUploads($dir) . '/' . $this->getPath($dir);
    }

    /**
     * Get is image.
     *
     * @return bool
     */
    public function isImage()
    {
        return $this->isFormat('image');
    }

    /**
     * Set school.
     *
     * @return Document
     */
    public function setSchool(School $school)
    {
        $this->school = $school;

        return $this;
    }

    /**
     * Get school.
     *
     * @return School
     */
    public function getSchool()
    {
        return $this->school;
    }

    /**
     * Get status.
     *
     * @return bool
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set status.
     *
     * @param $prefix
     *
     * @return Document
     */
    public function setPrefix($prefix)
    {
        $this->prefix = trim($prefix);

        return $this;
    }

    /**
     * Add student.
     *
     * @return Document
     */
    public function addStudent(Person $person)
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
     * Get persons.
     *
     * @return Collection|Person[]
     */
    public function getPersons()
    {
        return $this->persons;
    }

    /**
     * Add operation.
     *
     * @return Document
     */
    public function addOperation(Operation $operation)
    {
        $this->operations[] = $operation;

        return $this;
    }

    /**
     * Remove operation.
     */
    public function removeOperation(Operation $operation): void
    {
        $this->operations->removeElement($operation);
    }

    /**
     * Get operations.
     *
     * @return Collection
     */
    public function getOperations()
    {
        return $this->operations;
    }

    /**
     * Get code classe Font awesome Icon File.
     *
     * @return string
     */
    public function getFaIconFile()
    {
        switch ($this->mime) {
            case 'image/png':
            case 'image/jpeg':
                $faIcon = 'fa-file-image-o';
                break;
            case 'application/pdf':
                $faIcon = 'fa-file-pdf-o';
                break;
            case 'audio/mpeg':
                $faIcon = 'fa-file-audio-o ';
                break;

            default:
                $faIcon = 'fa-file-o';
                break;
        }

        return $faIcon;
    }

    /**
     * Get informations to document.
     *
     * @return array
     *
     * @throws ImagickException
     */
    public function getInfos()
    {
        return [
            'path' => $this->getWebPath(),
            'pathThumb' => $this->getWebPath(Document::DIR_THUMB),
            'pathPreview' => $this->getWebPath(Document::DIR_PREVIEW),
            'name' => $this->getName(),
            'id' => $this->getId(),
            'title' => $this->getTitle(),
        ];
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'ID : ' . $this->getId() .
            "\r\nNAME: " . $this->getName() .
            "\r\nCREATED: " . $this->getCreatedAt()->format('d/m/Y H:i:s') .
            "\r\nAUTHOR: " . $this->getAuthor()->getName();
    }

    /**
     * Add accountStatements.
     *
     * @return Document
     */
    public function addAccountStatement(AccountStatement $accountStatements)
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
     * Get accountStatements.
     *
     * @return Collection
     */
    public function getAccountStatements()
    {
        return $this->accountStatements;
    }

    /**
     * Add accountSlip.
     *
     * @return Document
     */
    public function addAccountSlip(AccountSlip $accountSlip)
    {
        $this->accountSlips[] = $accountSlip;

        return $this;
    }

    /**
     * Remove accountSlip.
     */
    public function removeAccountSlip(AccountSlip $accountSlip): void
    {
        $this->accountSlips->removeElement($accountSlip);
    }

    /**
     * Get accountSlips.
     *
     * @return Collection
     */
    public function getAccountSlips()
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
