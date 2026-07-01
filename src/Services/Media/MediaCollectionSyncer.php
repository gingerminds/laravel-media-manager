<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Services\Media;

use BackedEnum;
use Gingerminds\LaravelMediaManager\Models\Media\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MediaCollectionSyncer
{
    /**
     * @param BelongsToMany<Media, Model> $relation
     * @param array<int, int|string|null> $mediaIds
     */
    public function sync(BelongsToMany $relation, array $mediaIds, string|BackedEnum $collection): void
    {
        $collectionValue = $collection instanceof BackedEnum ? $collection->value : $collection;

        $pivotData = [];

        foreach (array_values(array_filter($mediaIds, fn ($id) => $id !== null && $id !== '')) as $index => $mediaId) {
            $pivotData[(int) $mediaId] = [
                'collection' => $collectionValue,
                'sort_order' => $index,
            ];
        }

        $relation->sync($pivotData);
    }
}
