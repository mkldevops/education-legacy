<?php

declare(strict_types=1);

namespace App\Manager;

use App\Exception\AppException;
use App\Repository\DocumentRepository;
use App\Repository\FamilyRepository;
use App\Repository\OperationRepository;
use App\Repository\PersonRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;

class DashboardManager
{
    public function __construct(
        private readonly OperationRepository $operationRepository,
        private readonly PersonRepository $personRepository,
        private readonly FamilyRepository $familyRepository,
        private readonly DocumentRepository $documentRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws AppException
     */
    public static function generateItemsOfMenu(string $route = null): array
    {
        $file = __DIR__.'/../../config/menu.yml';
        if (!is_file($file)) {
            throw new AppException(sprintf('No such file or directory : %s', $file));
        }

        // extract yaml file menu
        $menus = Yaml::parse(file_get_contents($file));
        $found = false;

        foreach ($menus as &$menu) {
            if (!isset($menu['sub'])) {
                continue;
            }

            foreach ($menu['sub'] as $key2 => &$menu2) {
                if ($route === $key2) {
                    $menu2['active'] = true;
                    $menu['active'] = true;
                    $found = true;

                    break;
                }

                if (!isset($menu2['sub'])) {
                    continue;
                }

                foreach ($menu2['sub'] as $key3 => &$menu3) {
                    if ($route === $key3) {
                        $menu3['active'] = true;
                        $menu2['active'] = true;
                        $menu['active'] = true;
                        $found = true;

                        break;
                    }
                }

                if ($found) {
                    break;
                }
            }

            if ($found) {
                break;
            }
        }

        return $menus;
    }

    public static function size(float $int): string
    {
        $si_prefix = ['B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB'];
        $base = 1024;
        $class = min((int) log($int, $base), \count($si_prefix) - 1);

        return sprintf('%1.2f', $int / $base ** $class).' '.$si_prefix[$class];
    }

    /**
     * @throws AppException
     */
    public function search(string $search = null): iterable
    {
        try {
            if (empty($search)) {
                throw new AppException('The search is empty');
            }

            yield 'operation' => $this->operationRepository->search($search);
            yield 'person' => $this->personRepository->search($search);
            yield 'family' => $this->familyRepository->search($search);
            yield 'document' => $this->documentRepository->search($search);
        } catch (\Exception $exception) {
            $this->logger->error(__METHOD__.' '.$exception->getMessage());

            throw new AppException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }
    }
}
