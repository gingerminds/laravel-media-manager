<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Models\Media;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Gingerminds\LaravelCore\Models\ResourceModelInterface;
use Gingerminds\LaravelMediaManager\ApiProvider\Media\MediaProvider;
use Gingerminds\LaravelMediaManager\Models\Basket\Basket;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * @property string $file_name
 */
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
        Basket::GROUP_READ,
    ])
)]
#[ApiProperty(property: 'name', serialize: new Groups([
    Media::GROUP_LIST,
    Media::GROUP_READ,
    Basket::GROUP_READ,
]))]
#[ApiProperty(property: 'file_name', serialize: new Groups([
    Media::GROUP_LIST,
    Media::GROUP_READ,
    Basket::GROUP_READ,
]))]
#[ApiProperty(property: 'mime_type', serialize: new Groups([
    Media::GROUP_LIST,
    Media::GROUP_READ,
    Basket::GROUP_READ,
]))]
#[ApiProperty(property: 'size', serialize: new Groups([
    Media::GROUP_LIST,
    Media::GROUP_READ,
    Basket::GROUP_READ,
]))]
/**
 * @property string $file_name
 */
class Media extends Model implements ResourceModelInterface
{
    protected $table = 'medias';

    public const string GROUP_LIST = 'media:list';

    public const string GROUP_READ = 'media:read';

    protected $fillable = [
        'name',
        'file_name',
        'mime_type',
        'size',
    ];
}
