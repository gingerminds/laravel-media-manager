# Installation

## Requirements

- PHP ^8.4
- [`gingerminds/laravel-core`](https://github.com/gingerminds/laravel-core) ^2.8 (provides the admin framework, API Platform integration, and the `ResourceResolver` pattern this package builds on)
- `league/glide` ^4.0 (installed automatically as a dependency, used for on-the-fly image resizing)
- The `zip` PHP extension (used by the basket ZIP download feature)

## 1. Require the package

```bash
composer require gingerminds/laravel-media-manager
```

The package's service provider (`LaravelMediaManagerServiceProvider`) is auto-discovered — there is nothing to add to `config/app.php` or `bootstrap/providers.php`. It also registers an internal `LaravelMediaManagerAuthServiceProvider` for you (policies, permissions), so you don't need to register that one either.

## 2. Publish the config file

```bash
php artisan vendor:publish --tag=gingerminds-media-manager-config
```

This creates `config/gingerminds-media-manager.php` in your application. See [Configuration](./Configuration.md) for the full list of options.

> You do **not** need to touch `config/filesystems.php`. The package merges its own `glide` disk definition (used to cache resized images) into your app's filesystem config automatically at boot time.

## 3. Register the package's models with API Platform

`Media`, `MediaCategory`, `File`, and `Basket` are exposed as API resources via PHP attributes (`#[ApiResource]`) directly on their model classes — API Platform needs to know where to scan for them. Add the package's `Models` directory to your `config/api-platform.php`:

```php
'resources' => [
    // ...your existing entries
    base_path('vendor/gingerminds/laravel-media-manager/src/Models'),
],
```

Without this, the routes described in [API](./API.md) simply won't be registered.

## 4. Run the migrations

```bash
php artisan migrate
```

This creates the tables the package needs: `medias`, `files`, `media_categories`, `baskets`, and the `basket_media` pivot table.

## 5. Publish the JS/SCSS assets

The package ships plain JavaScript and SCSS source files (no bundled/compiled build) that your application's own Vite/Mix pipeline is expected to compile. Publish them with:

```bash
php artisan vendor:publish --tag=gingerminds-assets
```

This copies:

- `resources/js` → `resources/js/vendor/gingerminds-media-manager`
- `resources/scss` → `resources/scss/vendor/gingerminds-media-manager`

into your application. From there, import the entry points from your own app's JS/SCSS entry files, for example:

```js
// resources/js/app.js
import './vendor/gingerminds-media-manager/app.js';
```

```scss
// resources/scss/app.scss
@import './vendor/gingerminds-media-manager/app';
```

A few things worth knowing about this publish step:

- It is a **plain file copy**, not a symlink. Re-running `vendor:publish` will silently overwrite any local edits you've made to the published copies (standard Laravel publish behavior — there's no `--force` guard).
- If you update the package (e.g. via Composer) and it ships JS/SCSS changes, you need to **re-publish and re-build** to see them — a `composer update` alone does not update the copies already sitting in your `resources/` tree.
- The JS components depend on Bootstrap's JS (`bootstrap`'s `Modal`) being available in your bundle, since file upload and media selection both use Bootstrap modals. The media selector also optionally uses a global `Sortable` (SortableJS) for drag-reordering, if it's loaded (`gingerminds-core`'s admin layout already includes it).

## 6. (Optional) Seed permissions

The package ships a permission seeder (Spatie permissions) with six entries: `view medias`, `edit medias`, `delete medias`, `view media_categories`, `edit media_categories`, `delete media_categories` (guard `web`). It is **not** run automatically — call it explicitly from your own seeder if you want these permissions created:

```php
$this->call(\Gingerminds\LaravelMediaManager\Database\Seeders\PermissionSeeder::class);
```

## 7. (Optional) Disable the basket feature

The shopping-basket / ZIP-download feature (see [Basket](./Basket.md)) is enabled by default. To turn it off entirely (no policy registration, no login enrichment, no basket routes exposed to consuming code):

```php
// config/gingerminds-media-manager.php
'basket' => [
    'enabled' => false,
],
```

## What you get out of the box

Once installed, migrated, and (optionally) built:

- Admin CRUD screens for media and media categories at `{admin_prefix}/medias` and `{admin_prefix}/media-categories` (protected by the same `gingerminds-core.auth` middleware as the rest of the admin area).
- Two reusable Blade components (`<x-gingerminds-media-manager::form.inputs.file>` and `<x-gingerminds-media-manager::form.inputs.media-select>`) for use in your own forms — see [Components](./Components.md).
- API Platform endpoints for `Media`, `MediaCategory`, `File` (including on-the-fly image resizing), and `Basket` — see [API](./API.md).
- A handful of services (`FileUploadService`, `ImageProcessor`, `GlideCacheService`, `MediaCollectionSyncer`) you can reuse from your own code — see [Services](./Services.md).
