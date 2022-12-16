<?php

namespace App\Serializer;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator(decorates: 'api_platform.jsonld.normalizer.item')]
final class JsonLdNormalizer extends AbstractNormalizerDecorator
{
}
