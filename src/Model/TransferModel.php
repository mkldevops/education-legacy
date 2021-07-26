<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: fardus
 * Date: 28/09/2017
 * Time: 22:20
 * PHP Version : 7.1.
 */

namespace App\Model;

use App\Entity\Account;
use App\Entity\AccountSlip;
use App\Entity\OperationGender;
use App\Entity\Structure;

/**
 * Class TransferModel.
 */
class TransferModel
{
    private ?\DateTimeInterface $date = null;

    private ?\App\Entity\OperationGender $gender = null;

    private ?string $reference = null;

    private ?string $uniqueId = null;

    private ?\App\Entity\AccountSlip $accountSlip = null;

    private ?\App\Entity\Account $accountCredit = null;

    private ?\App\Entity\Account $accountDebit = null;

    private ?\App\Entity\Structure $structure = null;

    private ?float $amount = null;

    private ?string $comment = null;

    /**
     * Get AccountCredit.
     */
    public function getAccountCredit(): Account
    {
        return $this->accountCredit;
    }

    /**
     * Set AccountCredit.
     */
    public function setAccountCredit(Account $accountCredit): self
    {
        $this->accountCredit = $accountCredit;

        return $this;
    }

    /**
     * Get Gender.
     */
    public function getGender(): OperationGender
    {
        return $this->gender;
    }

    /**
     * Set Gender.
     */
    public function setGender(OperationGender $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get Reference.
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * Set Reference.
     *
     * @param string $reference is the reference of transaction
     */
    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get UniqueId.
     */
    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }

    /**
     * Set UniqueId.
     */
    public function setUniqueId(string $uniqueId): self
    {
        $this->uniqueId = $uniqueId;

        return $this;
    }

    /**
     * Get AccountDebit.
     */
    public function getAccountDebit(): Account
    {
        return $this->accountDebit;
    }

    /**
     * Set AccountDebit.
     */
    public function setAccountDebit(Account $accountDebit): self
    {
        $this->accountDebit = $accountDebit;

        return $this;
    }

    /**
     * Get Structure.
     */
    public function getStructure(): Structure
    {
        return $this->structure;
    }

    /**
     * Set Structure.
     */
    public function setStructure(Structure $structure): self
    {
        $this->structure = $structure;

        return $this;
    }

    /**
     * Get AccountSlip.
     */
    public function getAccountSlip(): AccountSlip
    {
        return $this->accountSlip;
    }

    /**
     * Set AccountSlip.
     */
    public function setAccountSlip(AccountSlip $accountSlip): self
    {
        $this->accountSlip = $accountSlip;

        return $this;
    }

    /**
     * Get Date.
     */
    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    /**
     * Set Date.
     */
    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Set Amount.
     */
    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get Amount.
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Set Comment.
     */
    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get Comment.
     */
    public function getComment(): string
    {
        return $this->comment;
    }
}
