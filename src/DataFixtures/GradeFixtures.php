<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Grade;
use App\Exception\AppException;
use DateTime;
use Doctrine\Persistence\ObjectManager;

class GradeFixtures extends AbstractAppFixtures
{
    /**
     * @throws AppException
     */
    public function load(ObjectManager $manager): void
    {
        foreach (self::getData() as $id => $data) {
            $grade = (new Grade())
                ->setId($id)
                ->setName($data['name'])
                ->setDescription($data['description'])
                ->setCreatedAt(new DateTime())
                ->setEnable(true)
            ;

            $manager->persist($grade);

            $manager->flush();
            $this->addReference(self::getKey($id), $grade);
        }
    }
}
