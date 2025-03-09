<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\OperationGender;
use App\Exception\AppException;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Class Schools.
 *
 * @author  fardus
 */
class OperationGenderFixtures extends AbstractAppFixtures implements DependentFixtureInterface
{
    /**
     * @throws AppException
     */
    public function load(ObjectManager $objectManager): void
    {
        $datas = self::getData();

        foreach ($datas as $i => $data) {
            $gender = (new OperationGender())
                ->setName($data['name'])
                ->setEnable(true)
                ->setCode($data['code'])
            ;

            $objectManager->persist($gender);
            $objectManager->flush();

            $this->addReference(self::getKey($i), $gender);
        }
    }

    /**
     * @return class-string<SchoolFixtures>[]|class-string<StructureFixtures>[]
     */
    public function getDependencies(): array
    {
        return [
            SchoolFixtures::class,
            StructureFixtures::class,
        ];
    }
}
