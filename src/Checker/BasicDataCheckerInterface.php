<?php

declare(strict_types=1);

namespace App\Checker;

use App\Exception\UnexpectedResultException;

interface BasicDataCheckerInterface
{
    /**
     * @throws UnexpectedResultException
     */
    public function checkPackage(): void;

    /**
     * @throws UnexpectedResultException
     */
    public function checkClassSchool(): void;

    /**
     * @throws UnexpectedResultException
     */
    public function checkClassPeriod(): void;

    /**
     * @throws UnexpectedResultException
     */
    public function checkSchool(): void;

    /**
     * @throws UnexpectedResultException
     */
    public function checkPeriod(): void;
}
