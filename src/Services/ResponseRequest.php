<?php

declare(strict_types=1);

namespace App\Services;

use App\Model\ResponseModel;

class ResponseRequest extends ResponseModel
{

    public static function responseDefault(array $data = []): object
    {
        return (object)array_merge([
            'success' => true,
            'errors' => [],
            'data' => [],
            'message' => null,
        ], $data);
    }
}
