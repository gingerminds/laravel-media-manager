# Laravel Media Manager

Media library, file uploads, and image processing for Laravel projects built on `gingerminds/laravel-core`. It provides:

- Admin CRUD screens for a media library organized into categories.
- Two reusable Blade components: a file-upload field (with a drag-and-drop modal) and a media picker.
- On-the-fly image resizing via [Glide](https://glide.thephpleague.com/), with configurable named presets.
- API Platform endpoints for media, media categories, and files.
- An optional shopping-basket feature for collecting media items and downloading them together as a ZIP.

## Requirements

- PHP ^8.4
- `gingerminds/laravel-core` ^2.8
- The `zip` PHP extension

## Quick start

```bash
composer require gingerminds/laravel-media-manager
php artisan vendor:publish --tag=gingerminds-media-manager-config
php artisan vendor:publish --tag=gingerminds-assets
php artisan migrate
```

Then register the package's models with API Platform (see [Installation](docs/Installation.md#3-register-the-packages-models-with-api-platform)) and wire the published JS/SCSS into your own build.

## Documentation

- [Installation](docs/Installation.md)
- [Configuration](docs/Configuration.md)
- [Components](docs/Components.md) — the file upload and media-select Blade components
- [Services](docs/Services.md) — `FileUploadService`, `ImageProcessor`, `GlideCacheService`, `MediaCollectionSyncer`
- [API](docs/API.md) — admin routes and API Platform endpoints
- [Basket](docs/Basket.md) — the media basket / ZIP download feature
