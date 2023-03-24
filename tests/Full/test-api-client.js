import ApiError from "../../assets/utils/api/api-error";

// @TODO: Add CSRF and/or session checks to API calls

/**
  * The `TestApiClient` class handles all requests made to the Koalati API.
  *
  * In addition to making the HTTP requests, it also handles the formatting
  * of requests and responses, basic error handling and response status checks,
  * as well as basic performance and security optimizations.
  */
class TestApiClient {
	playwrightPage = null;
	jwt = null;

	constructor(playwrightPage) {
		this.playwrightPage = playwrightPage;
	}

	async #getJwt() {
		if (this.jwt) {
			return this.jwt;
		}

		let jwt = await this.playwrightPage.evaluate(() => {
			return localStorage.getItem("session_api_token");
		});

		if (!jwt) {
			jwt = await this.playwrightPage.evaluate(async () => {
				return await fetch("/internal-api/session-authentication", {
					headers: {
						"X-Requested-With": "XMLHttpRequest",
					},
				})
					.then(response => {
						return response.json();
					})
					.then(authData => {
						localStorage.setItem("session_api_token", authData.token);
						return authData.token;
					});
			});

		}

		this.jwt = jwt;

		return this.jwt;
	}

	static get ERROR_FLASH() {
		return "flash";
	}

	/**
	 * @param {string} method The HTTP method to use for the request.
	 * @param {string} endpoint The URL for the API endpoint (relative URL, e.g. `/api/projects`).
	 * @param {object} body The body of the request. Raw objects and FormData are accepted.
	 * @param {TestApiClient.ERROR_FLASH|function|null} errorCallback Specifies what to do when a user-friendly
	 * 	error message is returned by the API. Available options:
	 * 	- `TestApiClient.ERROR_FLASH`: automatically displays the error in a temporary Flash message, and then throws an `ApiError`. (default)
	 * 	- function: calls the provided callback with the error message as a parameter.
	 *  - null: throws an `ApiError` without providing any user feedback.
	 * @param {AbortController|null} abortController The abort controller to use for the request (optional)
	 * @returns {object|undefined} The response data object, or undefined if an error is returned from the API.
	 * @throws {ApiError} Thrown when the API reutnrs an error and the `errorCallback` is not a function.
	 */
	async #request(method, endpoint, body = {}, errorCallback = TestApiClient.ERROR_FLASH, abortController = null) {
		method = method.trim().toUpperCase();

		if (method == "DELETE") {
			body = null;
		}

		if (method == "GET") {
			endpoint += (endpoint.includes("?") ? "&" : "?") + new URLSearchParams(body).toString();
			body = null;
		} else if (body) {
			body = JSON.stringify(body);
		}

		const fetchOptions = {
			method,
			body,
			headers: {
				"X-Requested-With": "XMLHttpRequest",
				"Accept": "application/ld+json",
				"Authorization": `bearer ${await this.#getJwt()}`,
			},
		};

		if (method != "DELETE") {
			fetchOptions.headers["Content-Type"] = "application/json";
		}

		if (abortController) {
			fetchOptions.signal = abortController.signal;
		}

		// @TODO: Implement better pagination support
		const endpointUrl = new URL(endpoint, "https://localhost");
		endpointUrl.searchParams.set("pagination", "false");

		const response = await fetch(endpointUrl, fetchOptions);

		let responseData = {};

		try {
			const responseText = await response.text();

			// Sometimes the response may be empty, so we check for that before parsing it.
			if (responseText) {
				responseData = JSON.parse(responseText);
			}
		} catch (error) {
			if (env.APP_ENV == "test") {
				window.Flash.show("danger", JSON.stringify({ message: error.message, stack: error.stack }, null, 4));
			}

			if (errorCallback == TestApiClient.ERROR_FLASH) {
				window.Flash.show("danger", "api.flash.server_error");
			}

			throw error;
		}

		if (!response.ok) {
			const errorMessage = `${responseData["hydra:title"]} - ${responseData["hydra:description"]}`;

			if (typeof errorCallback == "function") {
				errorCallback(errorMessage);
				return;
			}

			if (errorCallback == TestApiClient.ERROR_FLASH) {
				window.Flash.show("danger", errorMessage);
			}

			throw new ApiError(errorMessage);
		}

		// Add the complete Response object
		responseData._response = response;

		return responseData;
	}

	/**
	 * @param {string} endpoint The route name of the API endpoint.
	 * @param {object} body The body of the request. Raw objects and FormData are accepted.
	 * @param {TestApiClient.ERROR_FLASH|function|null} errorCallback Specifies what to do when a user-friendly
	 * 	error message is returned by the API. Available options:
	 * 	- `TestApiClient.ERROR_FLASH`: automatically displays the error in a temporary Flash message, and then throws an `ApiError`. (default)
	 * 	- function: calls the provided callback with the error message as a parameter.
	 *  - null: throws an `ApiError` without providing any user feedback.
	 * @param {AbortController|null} abortController The abort controller to use for the request (optional)
	 * @returns {object|undefined} The response data object, or undefined if an error is returned from the API.
	 * @throws {ApiError} Thrown when the API reutnrs an error and the `errorCallback` is not a function.
	 */
	get(endpoint, body = {}, errorCallback = TestApiClient.ERROR_FLASH, abortController = null) {
		return this.#request("GET", endpoint, body, errorCallback, abortController);
	}

	/**
	 * @param {string} endpoint The route name of the API endpoint.
	 * @param {object} body The body of the request. Raw objects and FormData are accepted.
	 * @param {TestApiClient.ERROR_FLASH|function|null} errorCallback Specifies what to do when a user-friendly
	 * 	error message is returned by the API. Available options:
	 * 	- `TestApiClient.ERROR_FLASH`: automatically displays the error in a temporary Flash message, and then throws an `ApiError`. (default)
	 * 	- function: calls the provided callback with the error message as a parameter.
	 *  - null: throws an `ApiError` without providing any user feedback.
	 * @param {AbortController|null} abortController The abort controller to use for the request (optional)
	 * @returns {object|undefined} The response data object, or undefined if an error is returned from the API.
	 * @throws {ApiError} Thrown when the API reutnrs an error and the `errorCallback` is not a function.
	 */
	post(endpoint, body = {}, errorCallback = TestApiClient.ERROR_FLASH, abortController = null) {
		return this.#request("POST", endpoint, body, errorCallback, abortController);
	}

	/**
	 * @param {string} endpoint The route name of the API endpoint.
	 * @param {object} body The body of the request. Raw objects and FormData are accepted.
	 * @param {TestApiClient.ERROR_FLASH|function|null} errorCallback Specifies what to do when a user-friendly
	 * 	error message is returned by the API. Available options:
	 * 	- `TestApiClient.ERROR_FLASH`: automatically displays the error in a temporary Flash message, and then throws an `ApiError`. (default)
	 * 	- function: calls the provided callback with the error message as a parameter.
	 *  - null: throws an `ApiError` without providing any user feedback.
	 * @param {AbortController|null} abortController The abort controller to use for the request (optional)
	 * @returns {object|undefined} The response data object, or undefined if an error is returned from the API.
	 * @throws {ApiError} Thrown when the API reutnrs an error and the `errorCallback` is not a function.
	 */
	put(endpoint, body = {}, errorCallback = TestApiClient.ERROR_FLASH, abortController = null) {
		return this.#request("PUT", endpoint, body, errorCallback, abortController);
	}

	/**
	 * @param {string} endpoint The route name of the API endpoint.
	 * @param {object} body The body of the request. Raw objects and FormData are accepted.
	 * @param {TestApiClient.ERROR_FLASH|function|null} errorCallback Specifies what to do when a user-friendly
	 * 	error message is returned by the API. Available options:
	 * 	- `TestApiClient.ERROR_FLASH`: automatically displays the error in a temporary Flash message, and then throws an `ApiError`. (default)
	 * 	- function: calls the provided callback with the error message as a parameter.
	 *  - null: throws an `ApiError` without providing any user feedback.
	 * @param {AbortController|null} abortController The abort controller to use for the request (optional)
	 * @returns {object|undefined} The response data object, or undefined if an error is returned from the API.
	 * @throws {ApiError} Thrown when the API reutnrs an error and the `errorCallback` is not a function.
	 */
	patch(endpoint, body = {}, errorCallback = TestApiClient.ERROR_FLASH, abortController = null) {
		return this.#request("PATCH", endpoint, body, errorCallback, abortController);
	}

	/**
	 * @param {string} endpoint The route name of the API endpoint.
	 * @param {object} body The body of the request. Raw objects and FormData are accepted.
	 * @param {TestApiClient.ERROR_FLASH|function|null} errorCallback Specifies what to do when a user-friendly
	 * 	error message is returned by the API. Available options:
	 * 	- `TestApiClient.ERROR_FLASH`: automatically displays the error in a temporary Flash message, and then throws an `ApiError`. (default)
	 * 	- function: calls the provided callback with the error message as a parameter.
	 *  - null: throws an `ApiError` without providing any user feedback.
	 * @param {AbortController|null} abortController The abort controller to use for the request (optional)
	 * @returns {object|undefined} The response data object, or undefined if an error is returned from the API.
	 * @throws {ApiError} Thrown when the API reutnrs an error and the `errorCallback` is not a function.
	 */
	delete(endpoint, body = {}, errorCallback = TestApiClient.ERROR_FLASH, abortController = null) {
		return this.#request("DELETE", endpoint, body, errorCallback, abortController);
	}
}

export default TestApiClient;
