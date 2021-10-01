<?php

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Entity\Organization;
use App\Entity\Project;
use App\Mercure\TopicBuilder;
use App\Mercure\UpdateDispatcher;
use App\Security\OrganizationVoter;
use App\Security\ProjectVoter;
use App\Util\ClientMessageSerializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;
/*
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\Mercure\Discovery;
*/
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractApiController extends AbstractController
{
	public const STATUS_ERROR = 'error';
	public const STATUS_OKAY = 'ok';

	/**
	 * A Mercure topic that the client may want to subscribe to in order
	 * to receive live updates to the data returned by the current request.
	 *
	 * This topic will be sent in the response via the `suggested-mercure-topic` HTTP header.
	 */
	private ?string $suggestedMercureTopic = null;

	/**
	 * The duration for which the current request's response will be cached.
	 * This can be defined with `enableResponseCache()`.
	 */
	private int $cacheDuration = 0;

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.serializer)
	 */
	public function __construct(
		protected TopicBuilder $topicBuilder,
		protected UpdateDispatcher $updateDispatcher,
		protected ClientMessageSerializer $serializer,
		protected TranslatorInterface $translator,
		private RequestStack $requestStack,
		/*
		private Discovery $discovery,
		private Authorization $authorization,
		*/
	) {
	}

	/**
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 */
	protected function getProject(int | string | null $id, string $privilege = ProjectVoter::VIEW): ?Project
	{
		if (!$id) {
			return null;
		}

		if (!is_numeric($id)) {
			$id = $this->idHasher->decode($id)[0];
		}

		/**
		 * @var \App\Repository\ProjectRepository
		 */
		$repository = $this->getDoctrine()->getRepository(Project::class);
		$project = $repository->find($id);

		if (!$project) {
			$this->notFound()->send();
			exit;
		}

		if (!$this->isGranted($privilege, $project)) {
			$this->accessDenied()->send();
			exit;
		}

		// Save the project to session as the "current project". This is used in the projectShortcut() method.
		$session = $this->get('request_stack')->getSession();
		$session->set('koalati_current_project_id', $project->getId());

		return $project;
	}

	/**
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 */
	protected function getOrganization(int | string | null $id, string $privilege = OrganizationVoter::VIEW): ?Organization
	{
		if (!$id) {
			return null;
		}

		if (!is_numeric($id)) {
			$id = $this->idHasher->decode($id)[0];
		}

		/**
		 * @var \App\Repository\OrganizationRepository
		 */
		$repository = $this->getDoctrine()->getRepository(Organization::class);
		$organization = $repository->find($id);

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

	protected function setSuggestedMercureTopic(string $topic): static
	{
		$this->suggestedMercureTopic = $topic;

		return $this;
	}

	private function addSuggestedMercureTopicToResponse(Response $response): static
	{
		if ($this->suggestedMercureTopic) {
			$response->headers->set('suggested-mercure-topic', $this->suggestedMercureTopic);

			// @TODO: Add Mercure authorization cookie to API responses
			/*
			$response->headers->setCookie(
				$this->authorization->createCookie($this->requestStack->getCurrentRequest(),  ["http://example.com/books/1"])
			);
			*/
		}

		return $this;
	}

	/**
	 * Generates a JsonResponse for an API request that has encountered an error.
	 */
	protected function apiError(string $message, int $code = 400): JsonResponse
	{
		$response = new JsonResponse([
			'status' => self::STATUS_ERROR,
			'code' => $code,
			'message' => $message,
		]);

		$this->addSuggestedMercureTopicToResponse($response);

		return $response;
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
			'status' => self::STATUS_OKAY,
			'code' => $code,
			'data' => $this->serializer->serialize($data, $groups),
		]);

		$this->addSuggestedMercureTopicToResponse($response);

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
	protected function badRequest(): JsonResponse
	{
		return $this->apiError('Bad request.', 400);
	}

	protected function enableResponseCache(int $duration = 3600): self
	{
		$this->cacheDuration = $duration;

		return $this;
	}
}
