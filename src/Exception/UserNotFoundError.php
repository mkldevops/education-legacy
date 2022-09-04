<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class UserNotFoundError extends UserNotFoundException implements MainErrorInterface
{
}
