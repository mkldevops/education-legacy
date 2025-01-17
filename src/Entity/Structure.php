<?php

declare(strict_types=1);

namespace App\Entity;

use App\Trait\AddressEntityTrait;
use App\Trait\AuthorEntityTrait;
use App\Trait\CityEntityTrait;
use App\Trait\EnableEntityTrait;
use App\Trait\IdEntityTrait;
use App\Trait\NameEntityTrait;
use App\Trait\ZipEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: \App\Repository\StructureRepository::class)]
class Structure implements \Stringable
{
    use AddressEntityTrait;
    use AuthorEntityTrait;
    use CityEntityTrait;
    use EnableEntityTrait;
    use IdentityTrait;
    use NameEntityTrait;
    use SoftDeleteableEntity;
    use TimestampableEntity;
    use ZipEntityTrait;

    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $logo = null;

    #[ORM\ManyToOne(targetEntity: Member::class, cascade: ['persist'])]
    protected ?Member $president = null;

    #[ORM\ManyToOne(targetEntity: Member::class, cascade: ['persist'])]
    protected ?Member $treasurer = null;

    #[ORM\ManyToOne(targetEntity: Member::class, cascade: ['persist'])]
    protected ?Member $secretary = null;

    #[ORM\OneToMany(targetEntity: Member::class, mappedBy: 'structure')]
    protected Collection $members;

    #[ORM\Column(type: 'json')]
    protected array $options = [];

    #[ORM\OneToMany(targetEntity: Account::class, mappedBy: 'structure')]
    protected Collection $accounts;

    #[ORM\OneToMany(targetEntity: AccountSlip::class, mappedBy: 'structure')]
    protected Collection $accountSlips;

    public function __construct()
    {
        $this->enable = true;
        $this->accounts = new ArrayCollection();
        $this->accountSlips = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getPresident(): ?Member
    {
        return $this->president;
    }

    public function setPresident(Member $president): static
    {
        $this->president = $president;

        return $this;
    }

    public function getTreasurer(): ?Member
    {
        return $this->treasurer;
    }

    public function setTreasurer(Member $treasurer): self
    {
        $this->treasurer = $treasurer;

        return $this;
    }

    public function getSecretary(): ?Member
    {
        return $this->secretary;
    }

    /**
     * Set secretary.
     */
    public function setSecretary(Member $secretary): static
    {
        $this->secretary = $secretary;

        return $this;
    }

    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function setMembers(Collection $members): self
    {
        $this->members = $members;

        return $this;
    }

    public function addMember(?Member $member): static
    {
        if ($member instanceof \App\Entity\Member && !$this->members->contains($member)) {
            $this->members[] = $member;
        }

        return $this;
    }

    public function removeMember(Member $member): void
    {
        $this->members->removeElement($member);
    }

    /**
     * @return array<int, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array<int, mixed> $options
     */
    public function setOptions(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return Collection<int, Account>
     */
    public function getAccounts(): Collection
    {
        return $this->accounts;
    }

    public function setAccounts(ArrayCollection $accounts): static
    {
        $this->accounts = $accounts;

        return $this;
    }

    public function addAccount(Account $account): self
    {
        if (!$this->accounts->contains($account)) {
            $this->accounts[] = $account;
        }

        return $this;
    }

    public function removeAccount(Account $accounts): void
    {
        $this->accounts->removeElement($accounts);
    }

    /**
     * @return Collection<int, AccountSlip>
     */
    public function getAccountSlips(): Collection
    {
        return $this->accountSlips;
    }

    public function setAccountSlips(ArrayCollection $accountSlips): self
    {
        $this->accountSlips = $accountSlips;

        return $this;
    }
}
