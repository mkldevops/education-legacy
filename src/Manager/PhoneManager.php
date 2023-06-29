<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Person;
use App\Exception\AppException;
use Doctrine\ORM\EntityManagerInterface;

final readonly class PhoneManager
{
    /**
     * @var string
     */
    public const PERSON = 'person';

    /**
     * @var string
     */
    public const FATHER = 'father';

    /**
     * @var string
     */
    public const MOTHER = 'mother';

    /**
     * @var string
     */
    public const LEGAL_GUARDIAN = 'legalGuardian';

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Get All Phones.
     *
     * @return array{person: int[]|mixed[][]|null[], father?: int[]|mixed[][]|null[], mother?: int[]|mixed[][]|null[], legalGuardian?: int[]|mixed[][]|null[]}
     */
    public static function getAllPhones(Person $person): array
    {
        $phones = [
            self::PERSON => self::getPhones($person),
        ];

        if ($person->getFamily() instanceof \App\Entity\Family) {
            if ($person->getFamily()->getFather() instanceof \App\Entity\Person) {
                $phones[self::FATHER] = self::getPhones($person->getFamily()->getFather());
            }

            if ($person->getFamily()->getMother() instanceof \App\Entity\Person) {
                $phones[self::MOTHER] = self::getPhones($person->getFamily()->getMother());
            }

            if ($person->getFamily()->getLegalGuardian() instanceof \App\Entity\Person) {
                $phones[self::LEGAL_GUARDIAN] = self::getPhones($person->getFamily()->getLegalGuardian());
            }
        }

        return $phones;
    }

    /**
     * @return array{id: null|int, phones: string[]}
     */
    public static function getPhones(Person $person): array
    {
        return [
            'id' => $person->getId(),
            'phones' => $person->getListPhones(),
        ];
    }

    public static function stringPhonesToArray(?string $phones): array
    {
        $phones = explode(';', $phones ?? '');
        $phones = array_unique($phones);

        $list = [];
        foreach ($phones as $value) {
            $value = self::purgePhone($value);

            if (!empty($value)) {
                $list[base64_encode($value)] = $value;
            }
        }

        return $list;
    }

    /**
     * @throws AppException
     */
    public function updatePhone(Person $person, string $value, string $key = null): string
    {
        if (!empty($key)) {
            $person->removePhone($key);
        }

        $person->addPhone($value);

        $this->entityManager->persist($person);
        $this->entityManager->flush();

        return base64_encode(self::purgePhone($value));
    }

    /**
     * @throws AppException
     */
    public function deletePhone(Person $person, string $key): bool
    {
        $person->removePhone($key);

        $this->entityManager->persist($person);
        $this->entityManager->flush();

        return true;
    }

    private static function purgePhone(string $value): string
    {
        $value = preg_replace('#\\D+#', '', $value);
        $value = preg_replace('#(\\d{2})#', '$1 ', $value);

        return trim($value);
    }
}
