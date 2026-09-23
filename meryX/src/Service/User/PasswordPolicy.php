<?php

namespace App\Service\User;

final class PasswordPolicy
{
    public const MIN_LENGTH = 8;

    /**
     * @return string[] validation error messages, empty when the password is acceptable
     */
    public static function validate(string $password): array
    {
        $errors = [];

        if (mb_strlen($password) < self::MIN_LENGTH) {
            $errors[] = sprintf('Password must be at least %d characters long.', self::MIN_LENGTH);
        }

        if (!preg_match('/[A-Za-z]/', $password)) {
            $errors[] = 'Password must contain at least one letter.';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one digit.';
        }

        return $errors;
    }
}
