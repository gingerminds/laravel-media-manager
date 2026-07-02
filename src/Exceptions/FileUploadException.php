<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Exceptions;

use RuntimeException;

class FileUploadException extends RuntimeException
{
    public static function couldNotStore(): self
    {
        return new self('Failed to store uploaded file.');
    }
}
