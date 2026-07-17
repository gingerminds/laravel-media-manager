<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Services\File;

/**
 * Rationalizes verbose, standards-committee-length mime types — mainly
 * Office Open XML, legacy MS Office, and OpenDocument formats — into short
 * `application/<ext>` strings before they're stored on `File::mime_type`.
 * E.g. `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`
 * becomes `application/xlsx`.
 *
 * Deliberately keeps the `application/` prefix on every mapped entry:
 * `resources/views/pages/media/partials/list.blade.php` tests
 * `str_contains($file->mime_type, 'application')` to decide whether to show
 * a thumbnail instead of the raw file inline, and dropping the prefix would
 * silently break that check.
 *
 * Anything not in the map (images, `application/pdf`, `application/zip`,
 * already-short types...) passes through unchanged. Only applied at upload
 * time (`FileUploadService::store()`) — existing `File` rows keep whatever
 * raw mime type they were stored with until next replaced.
 */
class MimeTypeNormalizer
{
    /**
     * @var array<string, string>
     */
    private const MAP = [
        // Office Open XML (Word/Excel/PowerPoint 2007+)
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'application/docx',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.template'   => 'application/dotx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => 'application/xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.template'      => 'application/xltx',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'application/pptx',
        'application/vnd.openxmlformats-officedocument.presentationml.template'     => 'application/potx',
        'application/vnd.openxmlformats-officedocument.presentationml.slideshow'    => 'application/ppsx',

        // Legacy MS Office (pre-2007)
        'application/msword'            => 'application/doc',
        'application/vnd.ms-excel'      => 'application/xls',
        'application/vnd.ms-powerpoint' => 'application/ppt',

        // OpenDocument
        'application/vnd.oasis.opendocument.text'         => 'application/odt',
        'application/vnd.oasis.opendocument.spreadsheet'  => 'application/ods',
        'application/vnd.oasis.opendocument.presentation' => 'application/odp',
    ];

    public static function normalize(string $mimeType): string
    {
        return self::MAP[$mimeType] ?? $mimeType;
    }
}
