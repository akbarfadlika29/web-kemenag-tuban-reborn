<?php

namespace App\Support;

class AdminPagination
{
    public static function perPage(): int
    {
        $value = request()->query('per_page', '20');

        if (!is_string($value) && !is_int($value)) {
            return 20;
        }

        return in_array((string) $value, ['20', '50', '100'], true)
            ? (int) $value
            : 20;
    }
}