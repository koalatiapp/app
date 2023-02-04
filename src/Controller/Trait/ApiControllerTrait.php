<?php

namespace App\Controller\Trait;

use App\Entity\Organization;
use App\Entity\Project;
use App\Mercure\UpdateDispatcher;
use App\Repository\OrganizationRepository;
use App\Repository\ProjectRepository;
use App\Security\OrganizationVoter;
use App\Security\ProjectVoter;
use App\Util\ClientMessageSerializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;
use Symfony\Contracts\Translation\TranslatorInterface;

trait ApiControllerTrait
{
	protected UpdateDispatcher $updateDispatcher;
	protected ClientMessageSerializer $serializer;
	protected TranslatorInterface $translator;
	protected RequestStack $requestStack;
	protected ProjectRepository $projectRepository;
	protected OrganizationRepository $organizationRepository;

	/**
	 * The duration for which the current request's response will be cached.
	 * This can be defined with `enableResponseCache()`.
	 */
	private int $cacheDuration = 0;

	#[\Symfony\Contracts\Service\Attribute\Required]
	public function setDependencies(
		UpdateDispatcher $updateDispatcher,
		ClientMessageSerializer $serializer,
		TranslatorInterface $translator,
		RequestStack $requestStack,
		ProjectRepository $projectRepository,
		OrganizationRepository $organizationRepository,
	): void {
		$this->updateDispatcher = $updateDispatcher;
		$this->serializer = $serializer;
		$this->translator = $translator;
		$this->requestStack = $requestStack;
		$this->projectRepository = $projectRepository;
		$this->organizationRepository = $organizationRepository;
	}

	/**
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 */
	protected function getProject(int|string|null $id, string $privilege = ProjectVoter::VIEW): ?Project
	{
		if (!$id) {
			return null;
		}

		if (!is_numeric($id)) {
			$id = $this->idHasher->decode($id)[0];
		}

		$project = $this->projectRepository->find($id);

		if (!$project) {
			$this->notFound()->send();
			exit;
		}

		if (!$this->isGranted($privilege, $project)) {
			$this->accessDenied()->send();
			exit;
		}

		// Save the project to session as the "current project". This is used in the projectShortcut() method.
		$session = $this->requestStack->getSession();
		$session->set('koalati_current_project_id', $project->getId());

		return $project;
	}

	/**
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 */
	protected function getOrganization(int|string|null $id, string $privilege = OrganizationVoter::VIEW): ?Organization
	{
		if (!$id) {
			return null;
		}

		if (!is_numeric($id)) {
			$id = $this->idHasher->decode($id)[0];
		}

		$organization = $this->organizationRepository->find($id);

		if (!$organization) {
			$this->notFound()->send();
			exit;
		}

		if (!$this->isGranted($privilege, $organization)) {
			$this->accessDenied()->send();
			exit;
		}

		return $organization;
	}

	/**
	 * Generates a JsonResponse for an API request that has encountered an error.
	 */
	protected function apiError(string $message, int $code = 400): JsonResponse
	{
		return new JsonResponse([
			'status' => "error",
			'code' => $code,
			'message' => $message,
		]);
	}

	/**
	 * Generates a JsonResponse for a successful API request.
	 * The provided data will be automatically serialized to JSON.
	 *
	 * @param mixed             $data   Data to serialize
	 * @param array<int,string> $groups Field groups to include in the serialization (`default` is always included)
	 * @param int               $code   HTTP code of the response
	 */
	protected function apiSuccess(mixed $data = null, array $groups = [], int $code = 200): JsonResponse
	{
		$response = new JsonResponse([
			'status' => "ok",
			'code' => $code,
			'data' => $this->serializer->serialize($data, $groups),
		]);

		if ($this->cacheDuration > 0) {
			$response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true');
			$response->setPublic();
			$response->setMaxAge($this->cacheDuration);
		}

		return $response;
	}

	/**
	 * Returns a generic 404 not found error response.
	 */
	protected function notFound(string $message = 'This resource does not exist or could not be found.'): JsonResponse
	{
		return $this->apiError($message, 404);
	}

	/**
	 * Returns a generic access denied error response.
	 */
	protected function accessDenied(): JsonResponse
	{
		return $this->apiError('You do not have access to this resource.', 403);
	}

	/**
	 * Returns a generic bad request error response.
	 */
	protected function badRequest(?string $explanation = ''): JsonResponse
	{
		$message = 'Bad request.';

		if ($explanation) {
			$message .= ' '.$explanation;
		}

		return $this->apiError($message, 400);
	}

	protected function enableResponseCache(int $duration = 3600): self
	{
		$this->cacheDuration = $duration;

		return $this;
	}
}
