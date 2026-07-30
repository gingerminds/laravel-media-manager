<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Console\Commands;

use Gingerminds\LaravelMediaManager\Models\File\File;
use Gingerminds\LaravelMediaManager\Models\Media\Media;
use Gingerminds\LaravelMediaManager\Services\File\GlideCacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ClearGlideCacheCommand extends Command
{
    protected $signature = 'media-manager:cache:clear
        {--media=* : Only clear the cache for these Media ids, instead of the whole cache}
        {--force : Skip the confirmation prompt for a full cache purge}';

    protected $description = 'Clear cached, resized Glide image variants';

    public function __construct(private readonly GlideCacheService $glideCacheService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        /** @var array<int, string> $mediaIds */
        $mediaIds = $this->option('media');

        return $mediaIds !== [] ? $this->clearForMedia($mediaIds) : $this->clearAll();
    }

    /**
     * @param array<int, string> $mediaIds
     */
    private function clearForMedia(array $mediaIds): int
    {
        foreach ($mediaIds as $mediaId) {
            $media = Media::find($mediaId);

            if (!$media instanceof Media) {
                $this->warn("Media [{$mediaId}] not found, skipping.");

                continue;
            }

            foreach ([$media->file, $media->thumbnail] as $file) {
                if ($file instanceof File) {
                    $this->glideCacheService->clear($file);
                }
            }

            $this->info("Cleared Glide cache for media [{$mediaId}].");
        }

        return self::SUCCESS;
    }

    private function clearAll(): int
    {
        if (
            !$this->option('force') && !$this->confirm(
                'This will delete the entire Glide cache on every disk in use. Continue?'
            )
        ) {
            return self::SUCCESS;
        }

        /** @var Collection<int, string> $disks */
        $disks = File::query()->distinct()->pluck('disk');

        if ($disks->isEmpty()) {
            $disks = collect([config('gingerminds-media-manager.disk', 'public')]);
        }

        foreach ($disks as $disk) {
            $this->glideCacheService->clearAll($disk);

            $this->info("Cleared Glide cache on disk [{$disk}].");
        }

        return self::SUCCESS;
    }
}
