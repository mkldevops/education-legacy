<?php

declare(strict_types=1);

namespace App\EventListener;

use App\DataFixtures\AbstractAppFixtures;
use App\Entity\ClassPeriod;
use App\Entity\ClassSchool;
use App\Entity\Package;
use App\Entity\Period;
use App\Entity\School;
use Fardus\Traits\Symfony\Manager\EntityManagerTrait;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class CheckDataListener
{
    use EntityManagerTrait;

    public bool $checked = false;

    public function __construct(
        public SessionInterface $session,
        public UrlGeneratorInterface $urlGenerator,
        public TranslatorInterface $translator,
        public Security $security
    ) {
    }

    public function onKernelFinishRequest(FinishRequestEvent $event = null): void
    {
        if (null === $this->security->getUser()) {
            return;
        }

        if (!$this->checked) {
            $this->session->getFlashBag()->clear();
            $packages = $this->entityManager->getRepository(Package::class)->count(['enable' => true]);
            if (empty($packages)) {
                $this->session->getFlashBag()->add('danger', $this->trans('Package'));
            }
            $this->checkClasses();
            $periods = $this->entityManager->getRepository(Period::class)->getAvailable();
            if (empty($periods)) {
                $this->session->getFlashBag()->add('danger', $this->trans('Period'));
            }
            $this->checkSchool();
            $this->checked = true;
        }
    }

    private function trans(string $class, string $text = null): string
    {
        return $this->translator->trans(
            $class.($text ? '.'.$text : null),
            ['%url%' => $this->urlGenerator->generate('easyadmin', ['entity' => $class])],
            'check_data'
        );
    }

    private function checkClasses(): void
    {
        $classes = $this->entityManager->getRepository(ClassSchool::class)->count(['enable' => true]);
        if (empty($classes)) {
            $this->session->getFlashBag()->add('danger', $this->trans('ClassSchool'));
        } else {
            $classes = $this->entityManager->getRepository(ClassPeriod::class)->count(['enable' => true]);
            if (empty($classes)) {
                $this->session->getFlashBag()->add('danger', $this->trans('ClassPeriod'));
            }
        }
    }

    private function checkSchool(): void
    {
        $schools = $this->entityManager->getRepository(School::class)->count(['enable' => true]);
        if (empty($schools)) {
            $this->session->getFlashBag()->add('danger', $this->trans('School'));
        } else {
            $schools = $this->entityManager->getRepository(School::class)->count(['name' => AbstractAppFixtures::TODEFINE]);
            if (!empty($schools)) {
                $this->session->getFlashBag()->add('warning', $this->trans('School', 'define'));
            }
        }
    }
}
