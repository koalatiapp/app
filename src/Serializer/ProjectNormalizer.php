<?php

namespace App\Serializer;

use App\Entity\Project;
use App\Storage\ProjectStorage;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * Adds favicon and thumbnail URLs to the data using the `ProjectStorage`.
 */
class ProjectNormalizer implements ContextAwareNormalizerInterface
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private Serializer $simpleSerializer,
		private ProjectStorage $projectStorage
	) {
	}

	/**
	 * @param Project $project
	 */
	public function normalize($project, string $format = null, array $context = [])
	{
		$data = $this->simpleSerializer->normalize($project, $format, $context);

		// Add favicon and thumbnail URLs
		$data['faviconUrl'] = $this->projectStorage->faviconUrl($project);
		$data['thumbnailUrl'] = $this->projectStorage->thumbnailUrl($project);

		return $data;
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function supportsNormalization($data, string $format = null, array $context = [])
	{
		return $data instanceof Project;
	}
}
