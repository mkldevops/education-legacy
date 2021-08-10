<?php

declare(strict_types=1);

namespace App\Entity;

use App\Manager\DocumentManager;
use App\Traits\BaseEntityTrait;
use App\Traits\SchoolEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\IdEntity;
use Imagick;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DocumentRepository")
 */
class Document
{
    use BaseEntityTrait;
    use IdEntity;
    use SchoolEntityTrait;
    public const DIR_FILE = 'original';
    public const DIR_PREVIEW = 'previews';
    public const DIR_THUMB = 'thumbs';
    public const EXT_PNG = 'png';

    /**
     * @Assert\File(maxSize="60000000")
     */
    private ?\Symfony\Component\HttpFoundation\File\UploadedFile $file = null;

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
     * @var Person[]
     *
     * @ORM\OneToMany(targetEntity=Person::class, mappedBy="image", cascade={"remove"})
     */
    private array|\Doctrine\Common\Collections\Collection|\Doctrine\Common\Collections\ArrayCollection $persons;

    /**
     * @var Operation
     *
     * @ORM\ManyToMany(targetEntity=Operation::class, mappedBy="documents", cascade={"remove"})
     */
    private array|\Doctrine\Common\Collections\Collection|\App\Entity\Operation|\Doctrine\Common\Collections\ArrayCollection $operations;

    /**
     * @var Operation
     *
     * @ORM\ManyToMany(targetEntity=AccountStatement::class, mappedBy="documents", cascade={"remove"})
     */
    private array|\Doctrine\Common\Collections\Collection|\App\Entity\Operation|\Doctrine\Common\Collections\ArrayCollection $accountStatements;

    /**
     * @var AccountSlip
     *
     * @ORM\ManyToMany(targetEntity=AccountSlip::class, mappedBy="documents", cascade={"remove"})
     */
    private array|\Doctrine\Common\Collections\Collection|\App\Entity\AccountSlip|\Doctrine\Common\Collections\ArrayCollection $accountSlips;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $size = null;

    /**
     * Constrcutor.
     */
    public function __construct()
    {
        $this->accountStatements = new ArrayCollection();
        $this->operations = new ArrayCollection();
        $this->persons = new ArrayCollection();
        $this->accountSlips = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'ID : '.$this->getId().
            "\r\nNAME: ".$this->getName().
            "\r\nCREATED: ".$this->getCreatedAt()->format('d/m/Y H:i:s').
            "\r\nAUTHOR: ".$this->getAuthor()->getName();
    }

    public function setFile(UploadedFile $file = null): self
    {
        $this->file = $file;

        return $this;
    }

    public function getFile(): UploadedFile
    {
        return $this->file;
    }

    public function getAbsolutePath($dir = self::DIR_FILE): ?string
    {
        return null === $this->path ? null : DocumentManager::getPathUploads($dir).'/'.$this->getPath($dir);
    }

    /**
     * @return string
     *
     * @throws \ImagickException
     */
    public function getWebPath(string $dir = self::DIR_FILE)
    {
        $url = null;

        if (!empty($this->path)) {
            $url = DocumentManager::getPathUploads($dir).DIRECTORY_SEPARATOR.$this->getPath($dir);
        }

        if (!is_file($url)) {
            $result = (object) [];
            $this->generateImages($result);

            if ((self::DIR_THUMB === $dir && empty($result->thumb))
                || (self::DIR_PREVIEW === $dir && empty($result->preview))
            ) {
                $url = $this->getWebPath();
            }
        }

        return $url;
    }

    /**
     * Get WebPath.
     *
     * @return string
     *
     * @throws \ImagickException
     */
    public function getWebPathThumb()
    {
        return $this->getWebPath(self::DIR_THUMB);
    }

    /**
     * @return string
     *
     * @throws \ImagickException
     */
    public function getWebPathPreview()
    {
        return $this->getWebPath(self::DIR_PREVIEW);
    }

    /**
     * Get Upload Root Dir.
     *
     * @param string $dir
     */
    public static function getUploadRootDir($dir = self::DIR_FILE): string
    {
        $dir = DocumentManager::getPathUploads().DIRECTORY_SEPARATOR.self::getUploadDir($dir);
        //dd($dir);
        if (!file_exists($dir) && (!mkdir($dir, 0770, true) && !is_dir($dir))) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }

        return $dir;
    }

    public function hasThumb(): bool
    {
        return is_file($this->getAbsolutePath(self::DIR_THUMB));
    }

    /**
     * Get Upload Dir.
     *
     * @param string $dir
     *
     * @return string
     */
    public static function getUploadDir($dir = self::DIR_FILE)
    {
        if (is_null($dir) || !in_array($dir, [self::DIR_FILE, self::DIR_THUMB, self::DIR_PREVIEW], true)) {
            $dir = self::DIR_FILE;
        }

        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return $dir;
    }

    /**
     * Generate Images.
     *
     * @param array|\stdClass $result
     *
     * @return self
     *
     * @throws \ImagickException
     */
    public function generateImages(&$result)
    {
        if (empty($this->fileName)) {
            $this->fileName = $this->getFileName();
        }

        if (is_array($result)) {
            $result = (object) $result;
        }

        $filepath = self::getUploadRootDir().'/'.$this->path;

        // If file is not supported
        if (!is_file($filepath) || !$this->isFormat(['pdf', 'image'])) {
            return $this;
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

        if (!is_file(self::getUploadRootDir(self::DIR_PREVIEW).'/'.$this->fileName.'.'.self::EXT_PNG)) {
            // Genreration du preview
            $img->scaleImage(800, 0);
            $img->setImageFormat('png');
            $result->preview = $img->writeImage(self::getUploadRootDir(self::DIR_PREVIEW).'/'.$this->fileName.'.'.self::EXT_PNG);
        }

        if (!is_file(self::getUploadRootDir(self::DIR_THUMB).'/'.$this->fileName.'.'.self::EXT_PNG)) {
            // Generation du thumb
            $img->scaleImage(150, 0);
            $img->setImageFormat('png');
            $result->thumb = $img->writeImage(self::getUploadRootDir(self::DIR_THUMB).'/'.$this->fileName.'.'.self::EXT_PNG);
        }

        $img->clear();

        return $this;
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
     * Get is image.
     *
     * @param $formats
     *
     * @return bool
     */
    public function isFormat($formats)
    {
        $result = false;

        if (!is_array($formats)) {
            $formats = [$formats];
        }

        foreach ($formats as $format) {
            $str = strpos($this->getMime(), $format);
            $result = (false !== $str);

            if ($result) {
                break;
            }
        }

        return $result;
    }

    public function setMime(string $mime): self
    {
        $this->mime = $mime;

        return $this;
    }

    /**
     * Get mime.
     *
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * Set extension.
     *
     * @param string $extension
     *
     * @return Document
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Get extension.
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Set path.
     *
     * @return Document
     */
    public function setPath(string $path = null)
    {
        $this->path = null === $path && empty($this->path) ? $this->getFileName().'.'.$this->getExtension() : $path;

        return $this;
    }

    /**
     * Get path.
     *
     * @param string $dir
     *
     * @return string
     */
    public function getPath($dir = self::DIR_FILE)
    {
        $path = $this->path;

        if (self::DIR_FILE !== $dir) {
            $path = self::getPathPNG($path);
        }

        return $path;
    }

    /**
     * Get file name.
     *
     * @return string
     */
    public function getFileName()
    {
        if (empty($this->fileName)) {
            $this->fileName = str_replace(strrchr($this->path, '.'), '', $this->path);
        }

        return $this->fileName;
    }

    /**
     * Get path with extension PNG.
     *
     * @param $path
     *
     * @return string
     */
    public static function getPathPNG($path)
    {
        $ext = substr(strrchr($path, '.'), 1);

        return str_replace($ext, self::EXT_PNG, $path);
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
     * Get status.
     *
     * @return bool
     */
    public function getPrefix()
    {
        return $this->prefix;
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
     * @return Collection|\App\Entity\Person[]
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
     * @throws \ImagickException
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
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAccountSlips()
    {
        return $this->accountSlips;
    }

    public function setFileName(string $fileName): Document
    {
        $this->fileName = $fileName;

        return $this;
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
