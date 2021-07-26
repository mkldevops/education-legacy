<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Exception\AppException;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Yaml\Parser;


abstract class AppFixtures extends Fixture
{
    public const TODEFINE = 'todefine';

    /**
     * @throws AppException
     */
    public static function getData(): ?array
    {
        $pathFile = self::getPath();
        if (!file_exists($pathFile)) {
            throw new AppException(sprintf('Not found data fixtures %s', $pathFile));
        }

        $yaml = new Parser();

        return $yaml->parseFile($pathFile);
    }

    /**
     * @throws AppException
     */
    public static function getKey(string $key): string
    {
        if (!array_key_exists($key, self::getData())) {
            throw new AppException(sprintf('Not found key "%s" in %s', $key, self::getPath()));
        }

        return sprintf('%s_%s', static::class, $key);
    }

    /**
     * @return string|string[]
     */
    private static function getPath()
    {
        $name = (new \ReflectionClass(static::class))->getShortName();

        return str_replace('.php', '', sprintf('%s/data/%s.yaml', __DIR__, $name));
    }
}
