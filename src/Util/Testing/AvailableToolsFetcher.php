<?php

namespace App\Util\Testing;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AvailableToolsFetcher
{
	private const NPM_KEYWORD = 'koalati';
	private FilesystemAdapter $cache;

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.httpClient)
	 */
	public function __construct(
		private HttpClientInterface $httpClient,
	) {
		$this->cache = new FilesystemAdapter();
	}

	/**
	 * @return array<string,ToolPackage>
	 */
	public function getTools(): array
	{
		return $this->cache->get('automated_testing_available_tools', function (ItemInterface $item) {
			$item->expiresAfter(1800);
			$allowedTools = [];

			$npmPackages = $this->fetchPackagesFromNpm();
			$toolServiceDependencies = $this->getToolServiceDependencies();

			foreach ($npmPackages as $package) {
				if (in_array($package->name, $toolServiceDependencies)) {
					$allowedTools[$package->name] = $package;
				}
			}

			return $allowedTools;
		});
	}

	/**
	 * @return array<int,ToolPackage>
	 */
	private function fetchPackagesFromNpm(): array
	{
		$registryUrl = sprintf('https://registry.npmjs.org/-/v1/search?text=keywords:%s&size=250', urlencode(self::NPM_KEYWORD));
		$response = $this->httpClient->request('GET', $registryUrl);

		if (!$response->getStatusCode() == 200) {
			return [];
		}

		$results = $response->toArray()['objects'] ?? [];
		$packages = array_map(function ($package) {
			return new ToolPackage($package['package']['name'], $package['package']['description']);
		}, $results);

		return $packages;
	}

	/**
	 * Gets the list of dependencies from the Tool Service's package.json.
	 * This data is fetched straight from the official GitHub repo, in the master branch.
	 *
	 * @return array<int,string>
	 */
	private function getToolServiceDependencies(): array
	{
		$response = $this->httpClient->request('GET', 'https://raw.githubusercontent.com/koalatiapp/tools-service/master/package.json');
		$package = $response->toArray();

		return array_keys($package['dependencies'] ?? []);
	}
}
