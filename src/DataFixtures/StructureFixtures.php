<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Structure;
use App\Exception\AppException;
use Doctrine\Persistence\ObjectManager;

/**
 * Class Structures.
 *
 * @author  fardus
 */
class StructureFixtures extends AbstractAppFixtures
{
    /**
     * @throws AppException
     */
    public function load(ObjectManager $objectManager): void
    {
        foreach (self::getData() as $i => $item) {
            $entity = (new Structure())
                ->setCity($item['city'])
                ->setAddress($item['address'])
                ->setName($item['name'])
                ->setEnable(true)
            ;

            $objectManager->persist($entity);
            $objectManager->flush();

            $this->addReference(self::getKey($i), $entity);
        }
    }
}
