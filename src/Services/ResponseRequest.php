<?php

declare(strict_types=1);

namespace App\Services;

use App\Model\ResponseModel;

/**
 * @since 0.5
 *
 * @author Hamada Sidi Fahari <h.fahari@gmail.com>
 */
class ResponseRequest extends ResponseModel
{
    /**
     * Get response default.
     *
     * @param string[] $data
     */
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
