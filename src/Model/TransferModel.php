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
use DateTimeInterface;

/**
 * Class TransferModel.
 */
class TransferModel
{
    private ?DateTimeInterface $date = null;

    private ?OperationGender $gender = null;

    private ?string $reference = null;

    private ?string $uniqueId = null;

    private ?AccountSlip $accountSlip = null;

    private ?Account $accountCredit = null;

    private ?Account $accountDebit = null;

    private ?Structure $structure = null;

    private ?float $amount = null;

    private ?string $comment = null;


    public function getAccountCredit(): ?Account
    {
        return $this->accountCredit;
    }

    public function setAccountCredit(Account $accountCredit): self
    {
        $this->accountCredit = $accountCredit;

        return $this;
    }

    public function getGender(): ?OperationGender
    {
        return $this->gender;
    }

    public function setGender(?OperationGender $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }


    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }


    public function getUniqueId(): ?string
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
    public function getAccountDebit(): ?Account
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
    public function getStructure(): ?Structure
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
    public function getAccountSlip(): ?AccountSlip
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
    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    /**
     * Set Date.
     */
    public function setDate(DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get Amount.
     */
    public function getAmount(): ?float
    {
        return $this->amount;
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
     * Get Comment.
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * Set Comment.
     */
    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
