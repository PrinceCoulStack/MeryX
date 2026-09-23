<?php

namespace App\Exception;

class DomainValidationException extends \RuntimeException
{
    /**
     * @param array<string, array<int, string>> $errors
     */
    public function __construct(private readonly array $errors, string $message = 'Validation failed')
    {
        parent::__construct($message);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
