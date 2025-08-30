<?php

declare(strict_types=1);

namespace App\Checker;

use App\DataFixtures\AbstractAppFixtures;
use App\Exception\UnexpectedResultException;
use App\Repository\ClassPeriodRepository;
use App\Repository\ClassSchoolRepository;
use App\Repository\PackageRepository;
use App\Repository\PeriodRepository;
use App\Repository\SchoolRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class BasicDataChecker implements BasicDataCheckerInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TranslatorInterface $translator,
        private readonly ClassPeriodRepository $classPeriodRepository,
        private readonly ClassSchoolRepository $classSchoolRepository,
        private readonly PackageRepository $packageRepository,
        private readonly PeriodRepository $periodRepository,
        private readonly SchoolRepository $schoolRepository,
    ) {}

    /**
     * @throws UnexpectedResultException
     */
    public function checkPackage(): void
    {
        if (0 === $this->packageRepository->count(['enable' => true])) {
            throw new UnexpectedResultException($this->trans('Package'));
        }
    }

    /**
     * @throws UnexpectedResultException
     */
    public function checkClassSchool(): void
    {
        if (0 === $this->classSchoolRepository->count(['enable' => true])) {
            throw new UnexpectedResultException($this->trans('ClassSchool'));
        }
    }

    /**
     * @throws UnexpectedResultException
     */
    public function checkPeriod(): void
    {
        if ([] === $this->periodRepository->getAvailable()) {
            throw new UnexpectedResultException($this->trans('Period'));
        }
    }

    /**
     * @throws UnexpectedResultException
     */
    public function checkClassPeriod(): void
    {
        if (0 === $this->classPeriodRepository->count(['enable' => true])) {
            throw new UnexpectedResultException($this->trans('ClassPeriod'));
        }
    }

    /**
     * @throws UnexpectedResultException
     */
    public function checkSchool(): void
    {
        if (0 === $this->schoolRepository->count(['enable' => true])) {
            throw new UnexpectedResultException($this->trans('School'));
        }

        if (0 !== $this->schoolRepository->count(['name' => AbstractAppFixtures::TODEFINE])) {
            throw new UnexpectedResultException($this->trans('School', 'define'));
        }
    }

    private function trans(string $class, ?string $text = null): string
    {
        return $this->translator->trans(
            $class.($text ? '.'.$text : null),
            ['%url%' => $this->urlGenerator->generate('app_admin_dashboard_index', ['entity' => $class])],
            'check_data'
        );
    }
}
