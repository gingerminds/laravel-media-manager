# Configuration

All options live in `config/gingerminds-media-manager.php`, published as described in [Installation](./Installation.md).

## `disk` / `folder`

```php
'disk'   => env('MEDIA_MANAGER_DISK', 'public'),
'folder' => env('MEDIA_MANAGER_FOLDER', 'uploads'),
```

- `disk` — the Laravel filesystem disk uploaded media files are stored on.
- `folder` — the default subfolder new uploads are stored under, when no explicit folder is passed to `FileUploadService::store()`/`replace()` (see [Services](./Services.md)).

## `presets`

```php
'presets' => [
    'micro'     => ['w' => 25,   'h' => 25,  'fit' => 'crop',    'q' => 70],
    'thumbnail' => ['w' => 150,  'h' => 150, 'fit' => 'crop',    'q' => 80],
    'card'      => ['w' => 400,  'h' => 300, 'fit' => 'contain', 'q' => 85],
    'hero'      => ['w' => 1280, 'h' => 720, 'fit' => 'crop',    'q' => 90],
],
```

Named [Glide](https://glide.thephpleague.com/) image transforms. Each key becomes a valid `{format}` value for the `GET /api/files/{id}/{format}` endpoint (see [API](./API.md)) and for the `previewPreset` prop of the file upload component (see [Components](./Components.md)). You can add your own presets or change the parameters of the existing ones — any [Glide server parameter](https://glide.thephpleague.com/4.0/api/quick-reference/) is valid.

> Resized images are cached on a dedicated `glide` filesystem disk (backed by `storage/app/glide`), which the package wires into your app automatically — there is nothing to configure for that disk yourself. The cache is cleared automatically whenever the source file is replaced or deleted.

## `basket`

```php
'basket' => [
    'enabled'        => true,
    'claim_strategy' => 'merge', // merge | replace | ignore
    'owner_models'   => [],
    'storage_disk'   => 'local',
],
```

- `enabled` — turns the whole basket/cart feature on or off (policy registration, login enrichment, and the basket API endpoints). See [Basket](./Basket.md) for the full workflow.
- `claim_strategy` — how an anonymous basket's contents are merged when claimed by a logging-in user:
  - `merge` (default) — the anonymous basket's media items are added to the user's basket, keeping anything already in it.
  - `replace` — the user's basket contents are fully replaced by the anonymous basket's contents.
  - anything else (e.g. `ignore`) — the anonymous basket is discarded without merging.
- `owner_models`, `storage_disk` — reserved for future use; they are not currently read anywhere in the package.

## `resources`

```php
'resources' => [
    'media' => [
        'model'      => Media::class,
        'controller' => MediaController::class,
        'repository' => MediaRepository::class,
        'request'    => MediaRequest::class,
        'provider'   => MediaProvider::class,
    ],
    'media_category' => [
        'model'      => MediaCategory::class,
        'controller' => MediaCategoryController::class,
        'repository' => MediaCategoryRepository::class,
        'request'    => MediaCategoryRequest::class,
        'provider'   => MediaCategoryProvider::class,
    ],
],
```

`Media` and `MediaCategory` are resolved through `ResourceResolver`, the same override mechanism used throughout `gingerminds-laravel-core` — if your project extends either model, point these entries at your own classes instead of editing the package. See the `laravel-core` documentation for how the resolver and its override pattern work in detail; this package simply plugs its own two resources into it.
