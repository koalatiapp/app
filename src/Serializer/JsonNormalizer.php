<?php

namespace App\Serializer;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator(decorates: 'api_platform.serializer.normalizer.item')]
final class JsonNormalizer extends AbstractNormalizerDecorator
{
}
