<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\DataFixtures\AbstractAppFixtures;
use App\Exception\InvalidArgumentException;
use App\Exception\PeriodException;
use App\Fetcher\SessionFetcherInterface;
use App\Repository\ClassPeriodRepository;
use App\Repository\ClassSchoolRepository;
use App\Repository\PackageRepository;
use App\Repository\PeriodRepository;
use App\Repository\SchoolRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class FinishRequestSubscriber implements EventSubscriberInterface
{
    public bool $checked = false;

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
        private Security $security,
        private PeriodRepository $periodRepository,
        private SchoolRepository $schoolRepository,
        private PackageRepository $packageRepository,
        private ClassSchoolRepository $classSchoolRepository,
        private ClassPeriodRepository $classPeriodRepository,
        private FlashBagInterface $flashBag,
        private SessionFetcherInterface $sessionFetcher
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FinishRequestEvent::class => 'onKernelFinishRequest',
        ];
    }

    /**
     * @throws PeriodException
     * @throws InvalidArgumentException
     */
    public function onKernelFinishRequest(FinishRequestEvent $event = null): void
    {
        if ($this->checked || null === $this->security->getUser()) {
            return;
        }

        $this->sessionFetcher->getPeriodOnSession();

        $this->flashBag->clear();
        $packages = $this->packageRepository->count(['enable' => true]);
        if (0 === $packages) {
            $this->flashBag->add('danger', $this->trans('Package'));
        }

        $this->checkClasses();
        $periods = $this->periodRepository->getAvailable();
        if ([] === $periods) {
            $this->flashBag->add('danger', $this->trans('Period'));
        }

        $this->checkSchool();
        $this->checked = true;
    }

    private function trans(string $class, string $text = null): string
    {
        return $this->translator->trans(
            $class.($text ? '.'.$text : null),
            ['%url%' => $this->urlGenerator->generate('app_admin_dashboard_index', ['entity' => $class])],
            'check_data'
        );
    }

    private function checkClasses(): void
    {
        $classes = $this->classSchoolRepository->count(['enable' => true]);
        if (0 === $classes) {
            $this->flashBag->add('danger', $this->trans('ClassSchool'));

            return;
        }

        $classes = $this->classPeriodRepository->count(['enable' => true]);
        if (0 === $classes) {
            $this->flashBag->add('danger', $this->trans('ClassPeriod'));
        }
    }

    private function checkSchool(): void
    {
        $schools = $this->schoolRepository->count(['enable' => true]);
        if (0 === $schools) {
            $this->flashBag->add('danger', $this->trans('School'));

            return;
        }

        $schools = $this->schoolRepository->count(['name' => AbstractAppFixtures::TODEFINE]);
        if (0 !== $schools) {
            $this->flashBag->add('warning', $this->trans('School', 'define'));
        }
    }
}
