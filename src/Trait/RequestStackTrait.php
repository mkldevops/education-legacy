<?php

declare(strict_types=1);

namespace App\Trait;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Service\Attribute\Required;
use Webmozart\Assert\Assert;

trait RequestStackTrait
{
    #[Required]
    public RequestStack $requestStack;

    public function getSession(): Session
    {
        $session = $this->requestStack->getSession();
        Assert::isInstanceOf($session, Session::class);

        return $session;
    }

    public function getFlashBag(): FlashBagInterface
    {
        return $this->getSession()->getFlashBag();
    }
}
