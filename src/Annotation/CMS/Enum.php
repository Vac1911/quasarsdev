<?php

namespace App\Annotation\CMS;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Enum
{
    public function __construct(public array $values) {}

    public function validate(mixed $value): bool
    {
        return in_array($value, $this->values);
    }
}