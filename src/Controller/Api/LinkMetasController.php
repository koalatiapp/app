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
	 * @Route("", methods={"GET"}, name="", options={"expose": true})
	 */
	public function getMetas(Request $request, MetaFetcher $metaFetcher): JsonResponse
	{
		$url = $request->query->get('url');
		$urlHash = md5($url);
		$cache = new FilesystemAdapter();

		$metas = $cache->get("link_metas.$urlHash", function (ItemInterface $item) use ($metaFetcher, $url) {
			$item->expiresAfter(3600 * 24 * LinkMetasController::CACHE_DURATION_IN_DAYS);

			return $metaFetcher->getMetas($url);
		});

		return $this->apiSuccess($metas);
	}
}
