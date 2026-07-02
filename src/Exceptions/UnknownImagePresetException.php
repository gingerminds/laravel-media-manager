<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Exceptions;

use RuntimeException;

class UnknownImagePresetException extends RuntimeException
{
    public static function forPreset(string $preset): self
    {
        return new self("Unknown preset: {$preset}");
    }
}
