<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Exceptions;

use RuntimeException;

class ZipArchiveException extends RuntimeException
{
    public static function couldNotCreate(): self
    {
        return new self('Could not create zip archive.');
    }

    public static function couldNotClose(): self
    {
        return new self('Failed to close zip archive.');
    }
}
