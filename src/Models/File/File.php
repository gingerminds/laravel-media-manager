<?php

namespace Gingerminds\LaravelMediaManager\Models\File;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\OpenApi\Model\Parameter;
use Gingerminds\LaravelMediaManager\Http\Controllers\File\ShowFileController;
use Gingerminds\LaravelMediaManager\Http\Controllers\File\ShowFormattedFileController;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $disk
 * @property string $path
 * @property string $original_name
 * @property string $mime_type
 * @property int $size
 */
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/files/{id}',
            controller: ShowFileController::class,
            openapi: new OpenApiOperation(
                summary: 'Retrieve a file',
                description: 'Returns the raw file (image, document, etc.)',
            ),
            middleware: 'throttle:60,1'
        ),
        new Get(
            uriTemplate: '/files/{id}/{format}',
            controller: ShowFormattedFileController::class,
            openapi: new OpenApiOperation(
                summary: 'Retrieve a formatted image',
                description: 'Returns the image resized according to a predefined format: thumbnail, card, hero.',
                parameters: [
                    new Parameter(
                        name: 'format',
                        in: 'path',
                        description: 'Format\'s code',
                        required: true,
                        schema: [
                            'type' => 'string',
                        ],
                    ),
                ],
            ),
            middleware: 'throttle:60,1',
        ),
    ]
)]
class File extends Model
{
    use HasUuids;

    protected $table = 'files';

    protected $fillable = [
        'disk',
        'path',
        'mime_type',
        'original_name',
        'size',
    ];

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }
}
