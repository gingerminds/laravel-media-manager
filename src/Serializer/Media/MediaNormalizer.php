<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Serializer\Media;

use Gingerminds\LaravelMediaManager\Models\Media\Media;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MediaNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const string ALREADY_CALLED = 'MEDIA_NORMALIZER_ALREADY_CALLED';

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var array<string, mixed> $data */
        $data = $this->normalizer->normalize($object, $format, $context);

        $file = $object->relationLoaded('file') ? $object->file : $object->load('file')->file;

        if ($file !== null) {
            $data['file'] = $file->isImage()
                ? (string) $file->id
                : $file->path;
        } else {
            $data['file'] = null;
        }

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof Media;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Media::class => false];
    }
}
