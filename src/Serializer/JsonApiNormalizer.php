<?php

namespace App\Serializer;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator(decorates: 'api_platform.jsonapi.normalizer.item')]
final class JsonApiNormalizer extends AbstractNormalizerDecorator
{
}
