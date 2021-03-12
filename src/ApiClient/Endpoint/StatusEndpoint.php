<?php

namespace App\ApiClient\Endpoint;

class StatusEndpoint extends AbstractEndpoint
{
	/**
	 * Checks whether the tools API is up or down and returns its status.
	 */
	public function up(): bool
	{
		return $this->client->request('GET', '/status/up')['success'] ?? false;
	}

	/**
	 * Returns the uptime of the tools API.
	 */
	public function uptime(): int
	{
		$response = $this->client->request('GET', '/status/up');
		$uptime = $response['uptime'] ?? 0;

		return (int) ceil($uptime);
	}

	/**
	 * Returns the queue information of the tools API.
	 *
	 * @return array<string,int>
	 */
	public function queue(): array
	{
		$response = $this->client->request('GET', '/status/queue');

		return $response['data'];
	}

	/**
	 * Returns the number of pending requests (requests that are currently
	 * being processed and that will soon be completed) in the queue of the
	 * tools API.
	 */
	public function pendingRequests(): int
	{
		$queueStatus = $this->queue();

		return $queueStatus['pendingRequests'];
	}

	/**
	 * Returns the number of unassigned requests (requests that haven't yet
	 * started to be processed) in the queue of the tools API.
	 */
	public function unassignedRequests(): int
	{
		$queueStatus = $this->queue();

		return $queueStatus['unassignedRequests'];
	}

	/**
	 * Returns processing and waiting time estimates for each tool, for both
	 * low and high priority requests.
	 *
	 * @return array<mixed>
	 */
	public function timeEstimates(): array
	{
		$response = $this->client->request('GET', '/status/time-estimates');

		return $response['data'];
	}
}