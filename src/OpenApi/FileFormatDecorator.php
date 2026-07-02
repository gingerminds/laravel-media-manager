<?php

namespace Gingerminds\LaravelMediaManager\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\OpenApi;

class FileFormatDecorator implements OpenApiFactoryInterface
{
    public function __construct(
        private readonly OpenApiFactoryInterface $decorated
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $formats = array_keys(config('gingerminds-media-manager.presets', []));

        $paths = $openApi->getPaths();

        foreach ($paths->getPaths() as $path => $pathItem) {
            if (!str_contains($path, '{format}')) {
                continue;
            }

            $get = $pathItem->getGet();

            if ($get === null) {
                continue;
            }

            $parameters = array_map(
                function (Parameter $parameter) use ($formats) {
                    if ($parameter->getName() !== 'format') {
                        return $parameter;
                    }

                    return $parameter->withSchema([
                        'type' => 'string',
                        'enum' => $formats,
                    ]);
                },
                $get->getParameters()
            );

            $paths->addPath($path, $pathItem->withGet(
                $get->withParameters($parameters)
            ));
        }

        return $openApi;
    }
}
