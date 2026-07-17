# Services

These services are the building blocks behind the package's own controllers and components — you can inject and reuse them directly from your own code.

## `FileUploadService`

`Gingerminds\LaravelMediaManager\Services\File\FileUploadService`

Registered as a singleton, constructed with the `disk` and `folder` from [configuration](./Configuration.md). Handles storing, replacing, and deleting `File` records together with their underlying disk files.

```php
public function __construct(
    GlideCacheService $glideCacheService,
    string $disk = 'public',
    string $folder = 'uploads',
)
```

### `store(UploadedFile $file, ?string $folder = null): File`

Slugifies the original filename (keeping its extension), stores it on the configured disk under `$folder` (or the default folder from config), and creates the corresponding `File` row (`disk`, `path`, `mime_type`, `original_name`, `size`).

`mime_type` is stored through `MimeTypeNormalizer::normalize()` first, not `$file->getMimeType()` directly: verbose formats (Office Open XML, legacy MS Office, OpenDocument) get rationalized into a short `application/<ext>` string (e.g. `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet` → `application/xlsx`) rather than storing the raw, standards-committee-length value. Anything not in its map passes through unchanged. Only applied at upload time — existing `File` rows keep whatever they were stored with until next replaced. `Media::file_type` (the API-exposed property, see `Media::getFileTypeAttribute()`) just proxies `$this->file?->mime_type`, so it reflects this rationalized value too — a breaking change in shape for any API consumer that was matching on the old raw mime type.

Throws `Gingerminds\LaravelMediaManager\Exceptions\FileUploadException` if the underlying disk write fails.

### `replace(UploadedFile $file, ?File $existing, ?string $folder = null, ?callable $beforeDelete = null): File`

Stores a new file in place of an existing one. If `$existing` is not null and `$beforeDelete` is provided, it's called with the existing `File` first — typically used to dissociate a relation before the old row is deleted, avoiding foreign key issues:

```php
$file = $uploadService->replace(
    $request->file('file'),
    $media->file,
    'medias',
    function () use ($media) {
        $media->file()->dissociate();
        $media->save();
    }
);

$media->file()->associate($file);
$media->save();
```

This is exactly the pattern used by `MediaRepository::update()` and (in a consuming project) `ProductRepository::update()` for a `main_visual` field.

### `delete(?File $file): void`

Deletes a file's Glide cache, its physical file on disk, and its `File` row. Safe to call with `null` (no-op).

### `url(?File $file): ?string`

Returns the disk URL for a file, or `null` if the file is `null`.

## `ImageProcessor`

`Gingerminds\LaravelMediaManager\Services\Processor\ImageProcessor`

Wraps a [Glide](https://glide.thephpleague.com/) server configured against the disk from `config('gingerminds-media-manager.disk')`, using the [presets](./Configuration.md#presets) defined in config.

```php
public function process(string $path, string $preset): string
```

Resolves (generating and caching if needed) the resized version of the file at `$path` for the given `$preset` name, and returns the cached file's path. Throws `Gingerminds\LaravelMediaManager\Exceptions\UnknownImagePresetException` if `$preset` isn't a configured preset key.

This is what powers the `GET /api/files/{id}/{format}` endpoint (see [API](./API.md)) — you generally won't need to call it directly unless you're building your own image-serving logic.

## `GlideCacheService`

`Gingerminds\LaravelMediaManager\Services\File\GlideCacheService`

```php
public function clear(File $file): void
```

Deletes the cached, resized variants of a given file. Called automatically by `FileUploadService::delete()`/`replace()` whenever a file is removed or replaced, so you don't usually need to call this yourself — it's exposed mainly for cases where you manage a `File`'s lifecycle outside of `FileUploadService`.

## `MediaCollectionSyncer`

`Gingerminds\LaravelMediaManager\Services\Media\MediaCollectionSyncer`

A small helper for consuming apps that have their own ordered, "collection"-typed many-to-many relations to `Media` — for example a `Product` with separate `visuals`, `movies`, and `documents` collections sharing one pivot table.

```php
public function sync(BelongsToMany $relation, array $mediaIds, string|BackedEnum $collection): void
```

Filters out empty ids, then syncs the pivot table with `collection` and `sort_order` columns set from the given `$mediaIds` array (order preserved as `sort_order`):

```php
$this->mediaCollectionSyncer->sync(
    $product->visuals(),
    $request->input('visuals', []),
    ProductMediaCollection::Visual,
);
```

This assumes your own pivot table has `collection` and `sort_order` columns — that's a convention this helper supports, not something the package's own migrations create for you.
