<?php declare(strict_types=1);


namespace App\Lib;

final class Validator
{
    public static function email(string $email): bool
    {
        return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function dateYmd(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }
        $d = \DateTimeImmutable::createFromFormat('Y-m-d', $value);
        return $d !== false && $d->format('Y-m-d') === $value;
    }

    public static function maxLen(string $value, int $max): bool
    {
        return mb_strlen($value) <= $max;
    }
}

