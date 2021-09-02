<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\TypeOperation;
use App\Exception\AppException;
use Doctrine\Persistence\ObjectManager;

/**
 * Class TypesOperations.
 *
 * @author  fardus
 */
class TypeOperationFixtures extends AbstractAppFixtures
{
    /**
     * @throws AppException
     */
    public function load(ObjectManager $manager): void
    {
        foreach (self::getData() as $item) {
            $parent = null;
            if (!empty($item['PARENT'])) {
                $parent = $this->getReference(self::getKey($item['PARENT']));
            }

            $entity = (new TypeOperation())
                ->setid($item['ID'])
                ->setCode($item['CODE'])
                ->setDescription($item['DESCRIPTION'])
                ->setName($item['NAME'])
                ->setTypeAmount($item['TYPE_AMOUNT'])
                ->setIsInternalTransfert($item['INTERNAL_TRANSFERT'])
                ->setShortName($item['SHORT_NAME'])
                ->setStatus($item['STATUS'])
                ->setParent($parent);

            $manager->persist($entity);
            $manager->flush();

            $this->addReference(self::getKey((int) $item['ID']), $entity);
        }
    }
}
