<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Repository\PackageRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class PackageExistsValidator extends ConstraintValidator
{
    public function __construct(private readonly PackageRepository $packageRepository) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof PackageExists) {
            throw new UnexpectedTypeException($constraint, PackageExists::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!\is_scalar($value) && !(\is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedValueException($value, 'string');
        }

        $package = $this->packageRepository->find($value);

        if (!$package) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ id }}', (string) $value)
                ->addViolation()
            ;
        }
    }
}
