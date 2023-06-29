<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Person;
use App\Exception\AppException;
use App\Services\AbstractFullService;
use Doctrine\ORM\EntityManagerInterface;

final readonly class PhoneManager
{
    /**
     * @var string
     */
    final public const PERSON = 'person';

    /**
     * @var string
     */
    final public const FATHER = 'father';

    /**
     * @var string
     */
    final public const MOTHER = 'mother';

    /**
     * @var string
     */
    final public const LEGAL_GUARDIAN = 'legalGuardian';

    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {
    }

    /**
     * Get All Phones.
     */
    public static function getAllPhones(Person $person): array
    {
        $phones = [
            self::PERSON => self::getPhones($person),
        ];

        if (!empty($person->getFamily())) {
            if (!empty($person->getFamily()->getFather())) {
                $phones[self::FATHER] = self::getPhones($person->getFamily()->getFather());
            }

            if (!empty($person->getFamily()->getMother())) {
                $phones[self::MOTHER] = self::getPhones($person->getFamily()->getMother());
            }

            if (!empty($person->getFamily()->getLegalGuardian())) {
                $phones[self::LEGAL_GUARDIAN] = self::getPhones($person->getFamily()->getLegalGuardian());
            }
        }

        return $phones;
    }

    /**
     * @return array<string, int>|array<string, array>|null[]
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
