<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Repository\StudentRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class StudentExistsValidator extends ConstraintValidator
{
    public function __construct(private readonly StudentRepository $studentRepository) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof StudentExists) {
            throw new UnexpectedTypeException($constraint, StudentExists::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!\is_scalar($value) && !(\is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedValueException($value, 'string');
        }

        $student = $this->studentRepository->find($value);

        if (!$student) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ id }}', (string) $value)
                ->addViolation()
            ;
        }
    }
}
