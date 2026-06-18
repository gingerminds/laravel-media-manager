<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Models\Basket;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ArrayObject;
use Gingerminds\LaravelMediaManager\ApiProvider\Basket\BasketProvider;
use Gingerminds\LaravelMediaManager\Http\Controllers\Basket\BasketDownloadController;
use Gingerminds\LaravelMediaManager\Models\Media\Media;
use Gingerminds\LaravelMediaManager\StateProcessor\Basket\BasketAddMediaStateProcessor;
use Gingerminds\LaravelMediaManager\StateProcessor\Basket\BasketCreateStateProcessor;
use Gingerminds\LaravelMediaManager\StateProcessor\Basket\BasketDeleteStateProcessor;
use Gingerminds\LaravelMediaManager\StateProcessor\Basket\BasketRemoveMediaStateProcessor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * @property string $token
 * @property string|null $owner_type
 * @property int|null $owner_id
 */
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/baskets',
            openapi: new OpenApiOperation(
                summary: 'Create a basket',
                description: 'Creates an anonymous basket. If authenticated via Sanctum, 
                the basket is linked to the user. Pass `anonymous_token` 
                to claim and merge an existing anonymous basket.',
                requestBody: new RequestBody(
                    content: new ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'anonymous_token' => [
                                        'type'        => 'string',
                                        'format'      => 'uuid',
                                        'nullable'    => true,
                                        'description' => 'Token of an anonymous basket to claim after login.',
                                        'example'     => '550e8400-e29b-41d4-a716-446655440000',
                                    ],
                                ],
                            ],
                        ],
                    ]),
                    required: false,
                ),
            ),
            normalizationContext: ['groups' => [Basket::GROUP_READ]],
            input: false,
            processor: BasketCreateStateProcessor::class,
        ),
        new Get(
            uriTemplate: '/baskets/{token}',
            uriVariables: ['token' => new Link(fromClass: Basket::class, identifiers: ['token'])],
            openapi: new OpenApiOperation(
                summary: 'Get a basket',
                description: 'Returns the basket for the given token. Anonymous baskets are public. 
                User baskets require Sanctum authentication as the owner.',
            ),
            normalizationContext: ['groups' => [Basket::GROUP_READ]],
            provider: BasketProvider::class,
        ),
        new Delete(
            uriTemplate: '/baskets/{token}',
            uriVariables: ['token' => new Link(fromClass: Basket::class, identifiers: ['token'])],
            openapi: new OpenApiOperation(
                summary: 'Delete a basket',
                description: 'Deletes the basket. Anonymous baskets can be deleted without authentication. 
                User baskets require Sanctum authentication as the owner.',
            ),
            provider: BasketProvider::class,
            processor: BasketDeleteStateProcessor::class,
        ),
        new Post(
            uriTemplate: '/baskets/{token}/medias',
            uriVariables: ['token' => new Link(fromClass: Basket::class, identifiers: ['token'])],
            openapi: new OpenApiOperation(
                summary: 'Add medias to a basket',
                description: 'Adds one or more medias to the basket by their IDs. 
                Already present medias are not duplicated.',
                requestBody: new RequestBody(
                    content: new ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['media_ids'],
                                'properties' => [
                                    'media_ids' => [
                                        'type'        => 'array',
                                        'items'       => ['type' => 'integer'],
                                        'description' => 'IDs of the medias to add.',
                                        'example'     => [1, 2, 3],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
            normalizationContext: ['groups' => [Basket::GROUP_READ]],
            input: false,
            provider: BasketProvider::class,
            processor: BasketAddMediaStateProcessor::class,
        ),
        new Delete(
            uriTemplate: '/baskets/{token}/medias/{mediaId}',
            uriVariables: ['token' => new Link(fromClass: Basket::class, identifiers: ['token'])],
            status: 200,
            openapi: new OpenApiOperation(
                summary: 'Remove a media from a basket',
                description: 'Detaches a media from the basket by its numeric ID (`mediaId`). 
                Returns the updated basket.',
            ),
            normalizationContext: ['groups' => [Basket::GROUP_READ]],
            output: Basket::class,
            provider: BasketProvider::class,
            processor: BasketRemoveMediaStateProcessor::class,
        ),
        new Get(
            uriTemplate: '/baskets/{token}/download',
            uriVariables: ['token' => new Link(fromClass: Basket::class, identifiers: ['token'])],
            controller: BasketDownloadController::class,
            openapi: new OpenApiOperation(
                summary: 'Download basket as ZIP',
                description: 'Returns a ZIP archive of all media files in the basket, 
                then deletes the basket. Returns 422 if the basket is empty.',
            ),
            output: false,
            read: false,
        ),
    ],
)]
class Basket extends Model
{
    protected $table = 'baskets';

    public const string GROUP_READ = 'basket:read';

    protected $fillable = [
        'token',
        'owner_type',
        'owner_id',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    #[ApiProperty(identifier: true)]
    #[Groups([Basket::GROUP_READ])]
    public function getToken(): string
    {
        return $this->attributes['token'];
    }

    /** @return MorphTo<Model, $this> */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsToMany<Media, $this, Pivot, 'pivot'> */
    #[Groups([Basket::GROUP_READ])]
    public function medias(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'basket_media');
    }
}
