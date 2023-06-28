<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DiplomaRepository;
use App\Traits\AuthorEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Fardus\Traits\Symfony\Entity\NameEntityTrait;
use Fardus\Traits\Symfony\Entity\TimestampableEntityTrait;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Entity\File as EmbeddedFile;
use Vich\UploaderBundle\Entity\File as VichFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=DiplomaRepository::class)
 *
 * @Vich\Uploadable
 */
class Diploma
{
    use AuthorEntityTrait;
    use EnableEntityTrait;
    use IdEntityTrait;
    use NameEntityTrait;
    use SoftDeleteableEntity;
    use TimestampableEntityTrait;

    /**
     * @ORM\OneToMany(targetEntity=Period::class, mappedBy="diploma")
     */
    private Collection $periods;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(
     *     mapping="document_diploma",
     *     fileNameProperty="document.name",
     *     size="document.size",
     *     mimeType="document.mime"
     * )
     */
    private ?File $imageFile = null;

    /**
     * @ORM\Embedded(class=VichFile::class)
     */
    private ?EmbeddedFile $image = null;

    /**
     * @ORM\ManyToOne(targetEntity=Document::class)
     */
    private ?Document $document = null;

    public function __construct()
    {
        $this->periods = new ArrayCollection();
        $this->image = new EmbeddedFile();
    }

    /**
     * @return Collection|Period[]
     */
    public function getPeriods(): Collection
    {
        return $this->periods;
    }

    public function addPeriod(Period $period): self
    {
        if (!$this->periods->contains($period)) {
            $this->periods[] = $period;
            $period->setDiploma($this);
        }

        return $this;
    }

    public function removePeriod(Period $period): self
    {
        if ($this->periods->contains($period)) {
            $this->periods->removeElement($period);
            // set the owning side to null (unless already changed)
            if ($period->getDiploma() === $this) {
                $period->setDiploma(null);
            }
        }

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|UploadedFile $imageFile
     *
     * @throws \Exception
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImage(): ?EmbeddedFile
    {
        return $this->image;
    }

    public function setImage(EmbeddedFile $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function setDocument(?Document $document): self
    {
        $this->document = $document;

        return $this;
    }
}
