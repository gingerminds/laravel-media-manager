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
 *
 * Office Open XML formats *are* zip archives, and libmagic/finfo — what
 * `UploadedFile::getMimeType()` actually uses under the hood — detects them
 * by peeking at the zip's internal file listing rather than fully validating
 * it. A plain, unrelated `.zip` upload can occasionally still trip that
 * heuristic and come back labelled e.g. `...spreadsheetml.sheet`, which this
 * class would otherwise happily rubber-stamp as `application/xlsx`. When a
 * `$realPath` is given, we double-check for the one file every OOXML variant
 * actually requires (`OOXML_MARKERS`) before trusting the MAP entry, and
 * fall back to `application/zip` if it's missing — a real xlsx/docx/pptx
 * always has its marker, a generic zip essentially never does.
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

    /**
     * Zip entry that must exist for the corresponding OOXML mime type in
     * MAP to be trusted — see class docblock. Legacy MS Office (OLE
     * Compound File, not a zip) and OpenDocument (a zip, but not one
     * libmagic tends to misfire on the same way) aren't included: this is
     * specifically the false-positive libmagic is prone to.
     *
     * @var array<string, string>
     */
    private const OOXML_MARKERS = [
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'word/document.xml',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.template'   => 'word/document.xml',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => 'xl/workbook.xml',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.template'      => 'xl/workbook.xml',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'ppt/presentation.xml',
        'application/vnd.openxmlformats-officedocument.presentationml.template'     => 'ppt/presentation.xml',
        'application/vnd.openxmlformats-officedocument.presentationml.slideshow'    => 'ppt/presentation.xml',
    ];

    /**
     * @param string $mimeType The mime type as reported by the upstream
     *                          detector (`UploadedFile::getMimeType()`).
     * @param string|null $realPath Local path to the actual file, used to
     *                          verify an OOXML-flavored $mimeType before
     *                          trusting it (see class docblock). Pass null
     *                          to skip verification (e.g. path unavailable).
     */
    public static function normalize(string $mimeType, ?string $realPath = null): string
    {
        if ($realPath !== null && isset(self::OOXML_MARKERS[$mimeType])) {
            $hasMarker = self::zipHasEntry($realPath, self::OOXML_MARKERS[$mimeType]);

            if ($hasMarker === false) {
                // Confirmed openable as a zip, just without the marker file
                // the claimed OOXML flavor requires — a generic zip
                // libmagic's heuristic mistook for e.g. an xlsx.
                return 'application/zip';
            }

            if ($hasMarker === null) {
                // Not even openable as a zip, so the upstream mime type
                // guess is unreliable either way — leave it untouched
                // rather than mapping it to something we can't back up.
                return $mimeType;
            }
        }

        return self::MAP[$mimeType] ?? $mimeType;
    }

    /**
     * @return bool|null True if $entry is present, false if $path opens as
     *                    a zip but doesn't have it, null if $path can't be
     *                    opened as a zip at all.
     */
    private static function zipHasEntry(string $path, string $entry): ?bool
    {
        $zip = new \ZipArchive();

        if ($zip->open($path) !== true) {
            return null;
        }

        $found = $zip->locateName($entry) !== false;
        $zip->close();

        return $found;
    }
}
