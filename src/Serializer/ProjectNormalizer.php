<?php

namespace App\Serializer;

use App\Entity\Project;
use App\Storage\ProjectStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Adds favicon and thumbnail URLs to the data using the `ProjectStorage`.
 */
class ProjectNormalizer implements NormalizerInterface
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private readonly ContainerInterface $container,
		private readonly ProjectStorage $projectStorage
	) {
	}

	/**
	 * @param Project $project
	 *
	 * @return array<mixed>|string|int|float|bool|\ArrayObject<int|string,mixed>|null
	 */
	public function normalize($project, string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
	{
		$data = $this->container->get('DefaultNormalizer')->normalize($project, $format, $context);

		if (is_array($data)) {
			// Add favicon and thumbnail URLs
			$data['faviconUrl'] = $this->projectStorage->faviconUrl($project);
			$data['thumbnailUrl'] = $this->projectStorage->thumbnailUrl($project);
			$data['status'] = $project->getStatus();
		}

		return $data;
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 *
	 * @param array<mixed> $context
	 */
	public function supportsNormalization($data, string $format = null, array $context = []): bool
	{
		return $data instanceof Project;
	}
}
