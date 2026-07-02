# API

## Admin (web) routes

All admin routes are registered under `web` + `gingerminds-core.auth` middleware, prefixed with your configured `admin_prefix`, and named with a `gingerminds-media-manager.` prefix.

| Route | Purpose |
|---|---|
| `{admin_prefix}/medias` (full resource: index/create/store/edit/update/destroy) | Media library CRUD |
| `{admin_prefix}/media-categories` (full resource) | Media category CRUD, including the category tree view |

These are rendered by `MediaController` / `MediaCategoryController` (or whichever classes you've overridden in [Configuration](./Configuration.md#resources)) and are not intended to be called from outside the admin UI.

## API Platform resources

`Media`, `MediaCategory`, `File`, and `Basket` are declared as API resources via PHP attributes directly on the Eloquent models (no separate `routes/api.php`) — this is the same pattern used throughout `gingerminds-laravel-core`. Remember these only get registered once you've added the package's `Models` directory to `config/api-platform.php` (see [Installation](./Installation.md)).

### Media

| Method | Path | Description |
|---|---|---|
| `GET` | `/api/media` | Paginated, filterable list. Supports a `media_category_id` filter (multiple values) and free-text search on `name`. |
| `GET` | `/api/media/{id}` | A single media item. |

Exposed fields: `id`, `name`, `file_reference`, `file_size`, `thumbnail_reference`, `thumbnail_size`. `file_reference`/`thumbnail_reference` resolve to either the underlying file's UUID (for images, to be used with the `File` preset endpoint below) or its raw storage path (for non-image files).

### MediaCategory

| Method | Path | Description |
|---|---|---|
| `GET` | `/api/media-categories` | Paginated list (up to 500 items, 200 by default), used to populate category filters/selects. |
| `GET` | `/api/media-categories/{id}` | A single category. |

Exposed fields: `id`, `code`, `name`, `parent_id`, and (read operation only) `children`.

### File

| Method | Path | Description |
|---|---|---|
| `GET` | `/api/files/{id}` | Streams the raw file (any mime type), with its original filename. |
| `GET` | `/api/files/{id}/{format}` | Returns the file resized/processed through a named [preset](./Configuration.md#presets) — image files only. |

`{format}` must be one of the preset keys defined in config — by default `micro`, `thumbnail`, `card`, or `hero`. Requesting a non-image file, or a `{format}` that isn't a configured preset, returns a `400`/`404` respectively. Both endpoints are rate-limited (60 requests/minute). The generated OpenAPI documentation lists the valid `{format}` values as an enum, sourced from your configured presets.

### Basket

See [Basket](./Basket.md) for the full workflow — creating a basket, adding/removing media, and downloading its contents as a ZIP.
