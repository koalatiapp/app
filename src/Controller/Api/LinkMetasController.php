<?php

namespace App\Controller\Api;

use App\Util\Meta\MetaFetcher;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @Route("/api/link-metas", name="api_link_metas")
 */
class LinkMetasController extends AbstractApiController
{
	protected const CACHE_DURATION_IN_DAYS = 10;

	/**
	 * Fetches the meta data for a given URL and returns it as JSON.
	 *
	 * Query parameters:
	 * - `url` (string): URL of the page to get metadata from.
	 * - `cache` (bool): Whether cache is allowed or not. (defaults to `true`)
	 *
	 * @Route("", methods={"GET"}, name="", options={"expose": true})
	 */
	public function getMetas(Request $request, MetaFetcher $metaFetcher): JsonResponse
	{
		$url = $request->query->get('url');

		if (!$url) {
			return $this->badRequest('An URL from which to fetch metadata must be provided via the `url` parameter.');
		}

		$allowCache = (bool) $request->query->get('cache', '1');
		$urlHash = md5($url);
		$cache = new FilesystemAdapter();

		if (!$allowCache) {
			$cache->deleteItem("link_metas.$urlHash");
		}

		$metas = $cache->get("link_metas.$urlHash", function (ItemInterface $item) use ($metaFetcher, $url) {
			$item->expiresAfter(3600 * 24 * LinkMetasController::CACHE_DURATION_IN_DAYS);

			return $metaFetcher->getMetas($url);
		});

		return $this->enableResponseCache(3600 * 24 * LinkMetasController::CACHE_DURATION_IN_DAYS)
			->apiSuccess($metas);
	}
}
