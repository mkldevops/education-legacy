<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\AuthorEntityInterface;
use App\Entity\Interface\EntityInterface;
use App\Repository\UserRepository;
use App\Trait\AuthorEntityTrait;
use App\Trait\EnableEntityTrait;
use App\Trait\IdEntityTrait;
use App\Trait\NameEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, EntityInterface, AuthorEntityInterface
{
    use AuthorEntityTrait;
    use EnableEntityTrait;
    use IdEntityTrait;
    use NameEntityTrait;
    use SoftDeleteableEntity;
    use TimestampableEntity;

    /**
     * @var int
     */
    final public const USER_ROBOT = 0;

    /**
     * @var Collection<int, School>
     */
    #[ORM\ManyToMany(targetEntity: School::class)]
    protected Collection $schoolAccessRight;

    #[ORM\Column(type: Types::STRING, length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    #[ORM\Column(type: Types::STRING)]
    private string $password;

    private ?string $plainPassword = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $surname = null;

    #[ORM\Column(type: Types::STRING, nullable: true, unique: true)]
    private string $email;

    #[Ignore]
    #[ORM\Column(name: 'last_login', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastLogin = null;

    public function __construct()
    {
        $this->schoolAccessRight = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getNameComplete();
    }

    public function getNameComplete(): string
    {
        return \sprintf('%s %s', strtoupper((string) $this->name), ucfirst((string) $this->surname));
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(\DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * @return Collection<int, School>
     */
    public function getSchoolAccessRight(): Collection
    {
        return $this->schoolAccessRight;
    }

    public function addSchoolAccessRight(School $school): self
    {
        if (!$this->schoolAccessRight->contains($school)) {
            $this->schoolAccessRight[] = $school;
        }

        return $this;
    }

    public function removeSchoolAccessRight(School $school): self
    {
        if ($this->schoolAccessRight->contains($school)) {
            $this->schoolAccessRight->removeElement($school);
        }

        return $this;
    }

    public function addRole(string $role): self
    {
        $this->roles[] = $role;

        return $this->setRoles(array_unique($this->roles));
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }
}
