<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Models\Media;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Gingerminds\LaravelCore\Models\FilterableModelInterface;
use Gingerminds\LaravelCore\Models\ResourceModelInterface;
use Gingerminds\LaravelMediaManager\ApiProvider\Media\MediaProvider;
use Gingerminds\LaravelMediaManager\Models\Basket\Basket;
use Gingerminds\LaravelMediaManager\Models\File\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
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
        Basket::GROUP_READ
    ])
)]
#[ApiProperty(property: 'name', serialize: new Groups([
    Media::GROUP_LIST,
    Media::GROUP_READ,
    Basket::GROUP_READ
]))]
#[ApiProperty(
    property: 'file_reference',
    serialize: new Groups([
        Media::GROUP_LIST,
        Media::GROUP_READ,
        Basket::GROUP_READ
    ]),
)]
class Media extends Model implements ResourceModelInterface, FilterableModelInterface
{
    protected $table = 'medias';

    public const string GROUP_LIST = 'media:list';
    public const string GROUP_READ = 'media:read';

    protected $fillable = [
        'name',
        'file_id',
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
        return $this->belongsTo(File::class);
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

    public static function getFilters(): array
    {
        return [
            'media_category_id' => [
                'type'     => 'select-model',
                'label'    => 'gingerminds-media-manager::translation.media_categories.name_p',
                'model'    => MediaCategory::class,
                'multiple' => false,
                'display'  => 'name',
            ],
        ];
    }
}
