<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Period;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class Periods.
 *
 * @author  fardus
 */
class PeriodFixtures extends Fixture
{
    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $yearCurrent = ((int) date('Y')) - 1;
        $yearMax = $yearCurrent + 5;

        while ($yearCurrent <= $yearMax) {
            $period = new Period();
            $period->setBegin(new \DateTime($yearCurrent.'-09-01'))
                ->setEnd(new \DateTime(($yearCurrent + 1).'-08-31'))
                ->setComment('')
                ->setEnable(time() >= $period->getBegin()?->getTimestamp() && time() <= $period->getEnd()?->getTimestamp())
                ->setName($yearCurrent.'/'.($yearCurrent + 1))
            ;

            $manager->persist($period);
            ++$yearCurrent;
        }

        $manager->flush();
    }
}
