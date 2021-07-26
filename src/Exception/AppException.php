<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: fardus
 * Date: 21/03/2016
 * Time: 20:05.
 */

namespace App\Exception;

/**
 * Class AppException.
 */
class AppException extends \Exception
{
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getMessage();
    }
}
