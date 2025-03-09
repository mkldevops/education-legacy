<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\TypeOperation;
use App\Exception\AppException;
use Doctrine\Persistence\ObjectManager;

class TypeOperationFixtures extends AbstractAppFixtures
{
    /**
     * @throws AppException
     */
    public function load(ObjectManager $objectManager): void
    {
        foreach (self::getData() as $item) {
            $parent = null;
            if (!empty($item['PARENT'])) {
                $parent = $this->getReference(self::getKey($item['PARENT']), TypeOperation::class);
            }

            $entity = (new TypeOperation())
                ->setid($item['ID'])
                ->setCode($item['CODE'])
                ->setDescription($item['DESCRIPTION'])
                ->setName($item['NAME'])
                ->setTypeAmount($item['TYPE_AMOUNT'])
                ->setIsInternalTransfert((bool) $item['INTERNAL_TRANSFERT'])
                ->setShortName($item['SHORT_NAME'])
                ->setEnable((bool) $item['STATUS'])
                ->setParent($parent)
            ;

            $objectManager->persist($entity);
            $objectManager->flush();

            $this->addReference(self::getKey((int) $item['ID']), $entity);
        }
    }
}
