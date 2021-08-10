<?php

declare(strict_types=1);

namespace App\Entity;

use App\Traits\AuthorEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\AddressEntityTrait;
use Fardus\Traits\Symfony\Entity\CityEntityTrait;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Fardus\Traits\Symfony\Entity\NameEntityTrait;
use Fardus\Traits\Symfony\Entity\ZipEntityTrait;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StructureRepository")
 */
class Structure
{
    use IdentityTrait;
    use NameEntityTrait;
    use AuthorEntityTrait;
    use EnableEntityTrait;
    use TimestampableEntity;
    use SoftDeleteableEntity;
    use CityEntityTrait;
    use ZipEntityTrait;
    use AddressEntityTrait;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $logo = null;

    /**
     * @ORM\ManyToOne(targetEntity=Member::class, cascade={"persist"})
     */
    protected ?Member $president = null;

    /**
     * @ORM\ManyToOne(targetEntity=Member::class, cascade={"persist"})
     */
    protected ?Member $treasurer = null;

    /**
     * @ORM\ManyToOne(targetEntity=Member::class, cascade={"persist"})
     */
    protected ?Member $secretary = null;

    /**
     * @ORM\OneToMany(targetEntity=Member::class, mappedBy="structure")
     */
    protected Collection $members;

    /**
     * @ORM\Column(type="json")
     */
    protected array $options = [];

    /**
     * @ORM\OneToMany(targetEntity=Account::class, mappedBy="structure")
     */
    protected Collection $accounts;

    /**
     * @ORM\OneToMany(targetEntity=AccountSlip::class, mappedBy="structure")
     */
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

    public function setLogo(string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setPresident(Member $president): static
    {
        $this->president = $president;

        return $this;
    }

    public function getPresident(): ?Member
    {
        return $this->president;
    }

    public function setTreasurer(Member $treasurer): self
    {
        $this->treasurer = $treasurer;

        return $this;
    }

    public function getTreasurer(): ?Member
    {
        return $this->treasurer;
    }

    /**
     * Set secretary.
     *
     * @return Structure
     */
    public function setSecretary(Member $secretary)
    {
        $this->secretary = $secretary;

        return $this;
    }

    public function getSecretary(): ?Member
    {
        return $this->secretary;
    }

    public function setMembers(Collection $members): self
    {
        $this->members = $members;

        return $this;
    }

    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(?Member $member): static
    {
        if (null !== $member && !$this->members->contains($member)) {
            $this->members[] = $member;
        }

        return $this;
    }

    public function removeMember(Member $member): void
    {
        $this->members->removeElement($member);
    }

    public function setOptions(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return ArrayCollection|Account[]
     */
    public function getAccounts()
    {
        return $this->accounts;
    }

    /**
     * @param ArrayCollection|Account[] $accounts
     *
     * @return Structure
     */
    public function setAccounts(ArrayCollection $accounts)
    {
        $this->accounts = $accounts;

        return $this;
    }

    /**
     * Add accounts.
     */
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
     * @return ArrayCollection|AccountSlip[]
     */
    public function getAccountSlips(): Collection
    {
        return $this->accountSlips;
    }

    public function setAccountSlips(Collection $accountSlips): self
    {
        $this->accountSlips = $accountSlips;

        return $this;
    }
}
