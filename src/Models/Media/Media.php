<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Models\Media;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Gingerminds\LaravelCore\Models\FilterableModelInterface;
use Gingerminds\LaravelCore\Models\ResourceModelInterface;
use Gingerminds\LaravelCore\Models\SearchableModelInterface;
use Gingerminds\LaravelMediaManager\ApiProvider\Media\MediaProvider;
use Gingerminds\LaravelMediaManager\Models\Basket\Basket;
use Gingerminds\LaravelMediaManager\Models\File\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * @property int|null $file_id
 */
#[ApiResource(
    operations: [
        new GetCollection(
            paginationClientItemsPerPage: true,
            normalizationContext: ['groups' => [Media::GROUP_LIST]],
        ),
        new Get(
            normalizationContext: ['groups' => [Media::GROUP_READ]],
        ),
    ],
    provider: MediaProvider::class
)]
#[ApiProperty(
    identifier: true,
    property: 'id',
    serialize: new Groups([
        Media::GROUP_LIST,
        Media::GROUP_READ,
        Basket::GROUP_READ,
    ])
)]
#[ApiProperty(property: 'name', serialize: new Groups([
    Media::GROUP_LIST,
    Media::GROUP_READ,
    Basket::GROUP_READ,
]))]
#[ApiProperty(
    property: 'file_reference',
    serialize: new Groups([
        Media::GROUP_LIST,
        Media::GROUP_READ,
        Basket::GROUP_READ,
    ]),
)]
#[ApiProperty(
    property: 'file_size',
    serialize: new Groups([
        Media::GROUP_LIST,
        Media::GROUP_READ,
        Basket::GROUP_READ,
    ]),
)]
#[ApiProperty(
    property: 'thumbnail_reference',
    serialize: new Groups([
        Media::GROUP_LIST,
        Media::GROUP_READ,
        Basket::GROUP_READ,
    ]),
)]
#[ApiProperty(
    property: 'thumbnail_size',
    serialize: new Groups([
        Media::GROUP_LIST,
        Media::GROUP_READ,
        Basket::GROUP_READ,
    ]),
)]
class Media extends Model implements ResourceModelInterface, FilterableModelInterface, SearchableModelInterface
{
    protected $table = 'medias';

    public const string GROUP_LIST = 'media:list';
    public const string GROUP_READ = 'media:read';

    protected $fillable = [
        'name',
        'file_id',
        'thumbnail_id',
        'media_category_id',
    ];

    /**
     * @return array<string>
     */
    public function getFillable(): array
    {
        return $this->fillable;
    }

    /**
     * @return BelongsTo<File, $this>
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    /**
     * @return BelongsTo<File, $this>
     */
    public function thumbnail(): BelongsTo
    {
        return $this->belongsTo(File::class, 'thumbnail_id');
    }

    /**
     * @return BelongsTo<MediaCategory, $this>
     */
    public function mediaCategory(): BelongsTo
    {
        return $this->belongsTo(MediaCategory::class);
    }

    public function getFileReferenceAttribute(): ?string
    {
        /** @var File|null $file */
        $file = $this->file;

        if ($file === null) {
            return null;
        }

        return $file->isImage()
            ? (string) $file->id
            : $file->path;
    }

    public function getFileSizeAttribute(): ?int
    {
        return $this->file?->size;
    }

    public function getThumbnailReferenceAttribute(): ?string
    {
        /** @var File|null $file */
        $file = $this->thumbnail;

        if ($file === null) {
            return null;
        }

        return $file->isImage()
            ? (string) $file->id
            : $file->path;
    }

    public function getThumbnailSizeAttribute(): ?int
    {
        return $this->thumbnail?->size;
    }

    public static function getFilters(): array
    {
        return [
            'media_category_id' => [
                'type'     => 'select-model',
                'label'    => 'gingerminds-media-manager::translation.media_categories.name_p',
                'model'    => MediaCategory::class,
                'multiple' => true,
                'display'  => 'name',
            ],
        ];
    }

    /**
     * @return array<string>
     */
    public static function getSearchableFields(): array
    {
        return ['name'];
    }
}
