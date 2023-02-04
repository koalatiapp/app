<?php

namespace App\Serializer;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator(decorates: 'api_platform.hal.normalizer.item')]
final class JsonHalNormalizer extends AbstractNormalizerDecorator
{
}
