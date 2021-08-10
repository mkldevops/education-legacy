<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: fahari
 * Date: 10/08/18
 * Time: 14:40.
 */

namespace App\Manager;

use App\Entity\Person;
use App\Services\AbstractFullService;

/**
 * Description of class CourseManager.
 *
 * @author  fahari
 */
class PhoneManager extends AbstractFullService
{
    public const PERSON = 'person';
    public const FATHER = 'father';
    public const MOTHER = 'mother';
    public const LEGAL_GUARDIAN = 'legalGuardian';

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
     * Get Phones.
     *
     * @return array
     */
    public static function getPhones(Person $person)
    {
        return [
            'id' => $person->getId(),
            'phones' => $person->getListPhones(),
        ];
    }

    public static function stringPhonesToArray(?string $phones): array
    {
        if (empty($phones)) {
            return [];
        }

        $phones = explode(';', $phones);
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
     * @return string|string[]|null
     */
    private static function purgePhone(string $value): ?string
    {
        $value = preg_replace("#\D+#", '', $value);
        $value = preg_replace("#(\d{2})#", '$1 ', $value);

        return trim($value);
    }

    /**
     * updatePhone.
     *
     * @throws \Exception
     */
    public function updatePhone(Person $person, string $value, string $key = null): bool
    {
        if (!empty($key)) {
            $person->removePhone($key);
        }

        $person->addPhone($value);

        $this->getEntityManager()->persist($person);
        $this->getEntityManager()->flush();

        return base64_encode(self::purgePhone($value));
    }

    /**
     * updatePhone.
     *
     * @throws \Exception
     */
    public function deletePhone(Person $person, string $key): bool
    {
        $person->removePhone($key);

        $this->getEntityManager()->persist($person);
        $this->getEntityManager()->flush();

        return true;
    }
}
