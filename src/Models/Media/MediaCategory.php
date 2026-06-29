<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Models\Media;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Gingerminds\LaravelCore\Models\ResourceModelInterface;
use Gingerminds\LaravelMediaManager\ApiProvider\Media\MediaCategoryProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => [MediaCategory::GROUP_LIST]],
            policy: 'viewAny',
        ),
        new Get(
            normalizationContext: ['groups' => [MediaCategory::GROUP_READ]],
            policy: 'view',
        ),
    ],
    provider: MediaCategoryProvider::class,
)]
#[ApiProperty(
    identifier: true,
    property: 'id',
    serialize: new Groups([
        MediaCategory::GROUP_LIST,
        MediaCategory::GROUP_READ,
    ])
)]
#[ApiProperty(property: 'code', serialize: new Groups([
    MediaCategory::GROUP_LIST,
    MediaCategory::GROUP_READ,
]))]
#[ApiProperty(property: 'name', serialize: new Groups([
    MediaCategory::GROUP_LIST,
    MediaCategory::GROUP_READ,
]))]
#[ApiProperty(property: 'children', serialize: new Groups([
    MediaCategory::GROUP_READ,
]))]
class MediaCategory extends Model implements ResourceModelInterface
{
    public const string GROUP_LIST = 'media_categories:list';
    public const string GROUP_READ = 'media_categories:read';

    public function getFillable(): array
    {
        return [
            'code',
            'name',
            'parent_id',
        ];
    }

    /**
     * @return BelongsTo<MediaCategory, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MediaCategory::class, 'parent_id');
    }

    /**
     * @return HasMany<MediaCategory, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(MediaCategory::class, 'parent_id')->orderBy('name');
    }

    /**
     * @return HasMany<MediaCategory, $this>
     */
    public function adminChildren(): HasMany
    {
        return $this
            ->hasMany(MediaCategory::class, 'parent_id')
            ->orderBy('name')
            ->with('adminChildren');
    }
}
