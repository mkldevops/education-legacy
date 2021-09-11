<?php

declare(strict_types=1);

namespace App\Manager\Interfaces;

use App\Entity\Family;
use App\Entity\Operation;
use App\Entity\Period;
use App\Model\FamilyPaymentModel;

interface PaymentPackageStudentManagerInterface
{
    public function familyPayments(Operation $operation, Family $family, Period $period): FamilyPaymentModel;
}
