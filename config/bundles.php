<?php

declare(strict_types=1);

use DAMA\DoctrineTestBundle\DAMADoctrineTestBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle;
use Endroid\QrCodeBundle\EndroidQrCodeBundle;
use Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle;
use FOS\JsRoutingBundle\FOSJsRoutingBundle;
use Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle;
use Sentry\SentryBundle\SentryBundle;
use SpomkyLabs\PwaBundle\SpomkyLabsPwaBundle;
use Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MakerBundle\MakerBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\UX\Icons\UXIconsBundle;
use Symfony\UX\LiveComponent\LiveComponentBundle;
use Symfony\UX\StimulusBundle\StimulusBundle;
use Symfony\UX\TwigComponent\TwigComponentBundle;
use Twig\Extra\TwigExtraBundle\TwigExtraBundle;

return [
    MakerBundle::class => ['dev' => true],
    DoctrineBundle::class => ['all' => true],
    DoctrineMigrationsBundle::class => ['all' => true],
    FrameworkBundle::class => ['all' => true],
    TwigBundle::class => ['all' => true],
    SecurityBundle::class => ['all' => true],
    EasyAdminBundle::class => ['all' => true],
    MonologBundle::class => ['all' => true],
    WebProfilerBundle::class => ['dev' => true, 'test' => true],
    DebugBundle::class => ['dev' => true],
    EndroidQrCodeBundle::class => ['all' => true],
    FOSJsRoutingBundle::class => ['all' => true],
    DoctrineFixturesBundle::class => ['dev' => true, 'test' => true],
    TwigExtraBundle::class => ['all' => true],
    StofDoctrineExtensionsBundle::class => ['all' => true],
    SentryBundle::class => ['all' => true],
    NelmioAliceBundle::class => ['dev' => true, 'test' => true],
    FidryAliceDataFixturesBundle::class => ['dev' => true, 'test' => true],
    DAMADoctrineTestBundle::class => ['test' => true],
    TwigComponentBundle::class => ['all' => true],
    StimulusBundle::class => ['all' => true],
    LiveComponentBundle::class => ['all' => true],
    SpomkyLabsPwaBundle::class => ['all' => true],
    UXIconsBundle::class => ['all' => true],
];
