<?php

declare(strict_types=1);

use Rector\Core\ValueObject\PhpVersion;
use Rector\Core\Configuration\Option;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Symfony\Rector\ClassMethod\ActionSuffixRemoverRector;
use Rector\Symfony\Rector\MethodCall\AddFlashRector;
use Rector\Symfony\Rector\MethodCall\ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector;
use Rector\Symfony\Rector\MethodCall\FormIsValidRector;
use Rector\Symfony\Rector\ClassMethod\TemplateAnnotationToThisRenderRector;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    // get parameters
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_80);
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    // Define what rule sets will be applied
    $containerConfigurator->import(SetList::DEAD_CODE);
    $containerConfigurator->import(SetList::CODE_QUALITY);
    $containerConfigurator->import(SetList::TYPE_DECLARATION);

    // get services (needed for register a single rule)
    $services = $containerConfigurator->services();

    // register a single rule
    $services->set(TypedPropertyRector::class);
    $services->set(TypedPropertyRector::class);
    $services->set(ClassPropertyAssignToConstructorPromotionRector::class);
    $services->set(ActionSuffixRemoverRector::class);
    $services->set(AddFlashRector::class);
    $services->set(ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector::class);
    $services->set(FormIsValidRector::class);
    $services->set(TemplateAnnotationToThisRenderRector::class);

    $services->set(AnnotationToAttributeRector::class)
        ->call('configure', [[
            AnnotationToAttributeRector::ANNOTATION_TO_ATTRIBUTE => ValueObjectInliner::inline([
                new AnnotationToAttribute('Symfony\Component\Routing\Annotation\Route', null),
            ]),
        ]]);
};
