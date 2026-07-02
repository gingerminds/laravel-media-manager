# Blade Components

The package registers its views under the `gingerminds-media-manager` namespace, so every component is used as `<x-gingerminds-media-manager::...>`.

## `form.inputs.file`

A single- or multi-file upload field. It renders as a button that opens a modal containing a drag-and-drop dropzone; selected (or already-uploaded) files are shown as a preview grid above the button.

```blade
<x-gingerminds-media-manager::form.inputs.file
    id="file"
    :label="__('gingerminds-media-manager::translation.form.file')"
    accept="image/*,.pdf,.xlsx,video/mp4,.zip,application/zip"
    :existing-file="isset($media) ? $media->file : null"
/>
```

### Props

| Prop | Default | Description |
|---|---|---|
| `id` | *required* | Base id, used to derive the input, modal and preview element ids, and the submitted field name. |
| `label` | *required* | Field label. |
| `size` | `null` | `tiny` \| `sm` \| `lg` \| `xl` \| `null` — controls the Bootstrap column width class wrapping the field. |
| `required` | `true` | Marks the field as required (adds the `required` attribute and a visual asterisk). |
| `disabled` | `false` | Disables the field entirely (upload button and preview actions become inert). |
| `accept` | `null` | Comma-separated list of accepted MIME types/extensions, e.g. `"image/*,.pdf"`. Also used to build the human-readable "accepted types" hint shown in the dropzone. |
| `multiple` | `false` | Allow selecting more than one file. When `false`, the preview renders as a single full-width card instead of a grid of tiles. |
| `maxSize` | `5` | Maximum file size in megabytes. This is a **client-side** check only — files over the limit are flagged in the preview with a "too large" message, but you should still validate size server-side (e.g. in your `FormRequest`). |
| `helper` | `null` | Optional helper text rendered below the field. |
| `preview` | `true` | Whether to render the preview list of selected/existing files at all. |
| `existingFile` | `null` | The file currently attached, if any. Accepts either a `File` model instance or a raw storage path string (legacy usage). |
| `previewPreset` | `'thumbnail'` | Which [Glide preset](./Configuration.md#presets) to use when rendering the existing file's thumbnail (image files only). |

### Existing file preview and removal

When `existingFile` is set, it is shown in the preview alongside a "view" (eye icon) and a "remove" (cross icon) action. Removing an existing file does not delete anything by itself — it flags a hidden `{id}_remove` input with the value `1`. Your update logic should check this flag when no new file was uploaded, e.g.:

```php
$uploadedFile = $request->file('file');

if ($uploadedFile !== null) {
    // replace the existing file
} elseif ($request->boolean('file_remove')) {
    // the user explicitly cleared the field without picking a replacement
}
```

This is exactly the pattern used internally by `MediaRepository::update()` for the package's own `file` and `thumbnail` fields — see [Services](./Services.md).

## `form.inputs.media-select`

A picker for selecting one or more items from the media library (rather than uploading a new file). It opens a modal listing the library, searchable and filterable by category; selected items are shown as removable thumbnail "chips".

```blade
{{-- Single selection --}}
<x-gingerminds-media-manager::form.inputs.media-select
    id="translations_{{ $language->id }}_booklet_id"
    name="translations[{{ $language->id }}][booklet_id]"
    :label="__('Booklet')"
    :selected="$translation?->booklet"
/>

{{-- Multiple selection, locked to a single category --}}
<x-gingerminds-media-manager::form.inputs.media-select
    id="visuals"
    name="visuals"
    :multiple="true"
    :selected="$product->visuals"
    :filters="['media_category_id' => 12]"
/>
```

### Props

| Prop | Default | Description |
|---|---|---|
| `id` | *required* | Base id for the field and its modal. |
| `name` | `$id` | Submitted field name. Supports nested names, e.g. `translations[1][booklet_id]`. |
| `label` | `null` | Field label. |
| `multiple` | `false` | Allow selecting several media items. |
| `selected` | `null` | The currently selected item(s): a `Media` model, a `Collection` of `Media`, or `null`. |
| `required` | `false` | Marks the field as required. |
| `disabled` | `false` | Disables the field (hides the "select"/"add" button and remove actions). |
| `size` | `null` | `tiny` \| `sm` \| `lg` \| `xl` \| `null` — Bootstrap column width. |
| `helper` | `null` | Optional helper text. |
| `filters` | `[]` | Extra, fixed filters merged into every `/api/media` query, e.g. `['media_category_id' => 12]`. |
| `categoryCodes` | `[]` | Restricts which categories can be picked, by their stable `code` (not their numeric id). See below. |
| `endpoint` | `'/api/media'` | Media search endpoint. |
| `categoryEndpoint` | `'/api/media-categories'` | Category list endpoint. |
| `perPage` | `24` | Number of results per page (initial load and each "load more" page). |

### `categoryCodes` behavior

- Not set (default): every category is selectable, no restriction.
- Several codes: the category dropdown only proposes those categories (their relative hierarchy is preserved).
- A single code: the category dropdown disappears entirely, and results are pre-filtered and locked to that one category.

### Multi-selection ordering

When `multiple` is `true`, selected chips are drag-reorderable and posted in that order as `name[]` hidden inputs — useful when the receiving relation cares about order (see `MediaCollectionSyncer` in [Services](./Services.md)). Reordering requires SortableJS to be loaded globally (already the case in the `gingerminds-core` admin layout).

## A note on the admin screens

The package also ships its own full CRUD admin screens for media and media categories (under `{admin_prefix}/medias` and `{admin_prefix}/media-categories`). Those are internal, controller-rendered pages, not components meant to be embedded elsewhere — the two components above are what you use to plug media selection/upload into your own project's forms.
