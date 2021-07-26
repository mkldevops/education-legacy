<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Document;
use App\Entity\Family;
use App\Entity\Operation;
use App\Entity\Period;
use App\Entity\Person;
use App\Entity\School;
use App\Entity\Student;
use App\Exception\AppException;
use App\Services\AbstractFullService;
use DateInterval;
use DateTime;
use Exception;
use Ghunti\HighchartsPHP\Highchart;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DashboardManager.
 */
class DashboardManager extends AbstractFullService
{
    /**
     * Get highChartStatsNumberStudents.
     *
     * @throws Exception
     */
    public function highChartStatsNumberStudents(Period $period, School $school): Highchart
    {
        $manager = $this->getEntityManager();

        $registred = $manager->getRepository(Student::class)
            ->getStatsStudentRegistered($school, $period)
        ;
        $desactivated = $manager->getRepository(Student::class)
            ->getStatsStudentDeactivated($school, $period)
        ;

        $data = (object) ['registred' => [], 'desactivated' => [], 'average' => []];
        $tmp = array_merge($registred, $desactivated);
        ksort($tmp);
        $current = new DateTime(key($tmp));

        $chart = new Highchart();
        $chart->chart->renderTo = 'stats-number-students';
        $chart->title->text = 'Stats Number Students';

        while ($current->getTimeStamp() <= time()) {
            $key = $current->format('Y-m');
            $chart->xAxis->categories[] = $current->format('M Y');

            $data->registred[] = $nbRregistred = isset($registred[$key]) ? $registred[$key] : 0;
            $data->desactivated[] = $nbDesactivated = isset($desactivated[$key]) ? -$desactivated[$key] : 0;
            $data->average[] = $nbRregistred + $nbDesactivated;

            $current->add(new DateInterval('P1M'));
        }

        $chart->series[] = [
            'type' => 'column',
            'name' => 'Registred',
            'data' => $data->registred,
        ];

        $chart->series[] = [
            'type' => 'column',
            'name' => 'Desactivated',
            'data' => $data->desactivated,
        ];

        $chart->series[] = [
            'type' => 'spline',
            'name' => 'Average',
            'data' => $data->average,
        ];

        return $chart;
    }

    /**
     * @throws Exception
     */
    public static function generateItemsOfMenu(string $route = null): array
    {
        $file = __DIR__.'/../../config/menu.yml';
        if (!is_file($file)) {
            throw new AppException("No such file or directory : $file");
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

    public function search(string $search = null): array
    {
        $result = [];

        try {
            if (empty($search)) {
                throw new AppException('The search is empty');
            }

            $result['operation'] = $this->entityManager
                ->getRepository(Operation::class)
                ->search($search);

            $result['person'] = $this->entityManager
                ->getRepository(Person::class)
                ->search($search);

            $result['family'] = $this->entityManager
                ->getRepository(Family::class)
                ->search($search);

            $result['document'] = $this->entityManager
                ->getRepository(Document::class)
                ->search($search);
        } catch (Exception $exception) {
            $this->logger->error(__METHOD__.' '.$exception->getMessage());
        }

        return $result;
    }

    public static function size(int $int)
    {
        $si_prefix = ['B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB'];
        $base = 1024;
        $class = min((int) log($int, $base), count($si_prefix) - 1);

        return sprintf('%1.2f', $int / $base ** $class).' '.$si_prefix[$class];
    }
}
