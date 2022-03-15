import ApiError from "./api-error";

// @TODO: Add CSRF and/or session checks to API calls

/**
  * The `ApiClient` class handles all requests made to the internal Koalati API.
  *
  * In addition to making the HTTP requests, it also handles the formatting
  * of requests and responses, basic error handling and response status checks,
  * as well as basic performance and security optimizations.
  */
class ApiClient {
	static get ERROR_FLASH()
	{
		return "flash";
	}

	/**
	 * Builds the URL for the requests and subscriptions.
	 *
	 * @param {string} method The HTTP method to use for the request.
	 * @param {string} endpoint The route name of the API endpoint.
	 * @param {object} body The body of the request. Raw objects and FormData are accepted.
	 */
	_resolveRouteUrl(method, endpoint, body = {})
	{
		let routeParams = body;

		if (body instanceof FormData) {
			routeParams = {};
			body.forEach((value, key) => routeParams[key] = value);
		}

		method = method.trim().toUpperCase();
		return Routing.generate(endpoint, routeParams);
	}

	/**
	 * @param {string} method The HTTP method to use for the request.
	 * @param {string} endpoint The route name of the API endpoint.
	 * @param {object} body The body of the request. Raw objects and FormData are accepted.
	 * @param {ApiClient.ERROR_FLASH|function|null} errorCallback Specifies what to do when a user-friendly
	 * 	error message is returned by the API. Available options:
	 * 	- `ApiClient.ERROR_FLASH`: automatically displays the error in a temporary Flash message, and then throws an `ApiError`. (default)
	 * 	- function: calls the provided callback with the error message as a parameter.
	 *  - null: throws an `ApiError` without providing any user feedback.
	 * @param {AbortController|null} abortController The abort controller to use for the request (optional)
	 * @returns {object|undefined} The response data object, or undefined if an error is returned from the API.
	 * @throws {ApiError} Thrown when the API reutnrs an error and the `errorCallback` is not a function.
	 */
	async _request(method, endpoint, body = {}, errorCallback = ApiClient.ERROR_FLASH, abortController = null)
	{
		method = method.trim().toUpperCase();

		if (!(body instanceof FormData) && !["GET", "DELETE"].includes(method)) {
			const formData = new FormData();

			for (const key in body) {
				formData.append(key, body[key]);
			}

			body = formData;
		}

		const url = this._resolveRouteUrl(method, endpoint, body);
		const fetchOptions = {
			method: method,
		};

		if (abortController) {
			fetchOptions.signal = abortController.signal;
		}

		if (!["GET", "DELETE"].includes(method)) {
			fetchOptions.body = body;
		}

		const response = await fetch(url, fetchOptions);
		let responseData;

		// If the request was redirect to a login page... redirect to a login page.
		if (response.redirected && response.url.includes("login")) {
			window.location.href = response.url;
		}

		try {
			responseData = await response.json();
		} catch (error) {
			window.Flash.show("danger", "api.flash.server_error");
			throw error;
		}

		if (responseData.status != "ok") {
			if (typeof errorCallback == "function") {
				errorCallback(responseData.message);
				return;
			}

			if (errorCallback == ApiClient.ERROR_FLASH) {
				window.Flash.show("danger", responseData.message);
			}

			throw new ApiError(responseData.message);
		}

		// Add the complete Response object
		responseData._response = response;

		return responseData;
	}

	/**
	 * @param {string} endpoint The route name of the API endpoint.
	 * @param {object} body The body of the request. Raw objects and FormData are accepted.
	 * @param {ApiClient.ERROR_FLASH|function|null} errorCallback Specifies what to do when a user-friendly
	 * 	error message is returned by the API. Available options:
	 * 	- `ApiClient.ERROR_FLASH`: automatically displays the error in a temporary Flash message, and then throws an `ApiError`. (default)
	 * 	- function: calls the provided callback with the error message as a parameter.
	 *  - null: throws an `ApiError` without providing any user feedback.
	 * @param {AbortController|null} abortController The abort controller to use for the request (optional)
	 * @returns {object|undefined} The response data object, or undefined if an error is returned from the API.
	 * @throws {ApiError} Thrown when the API reutnrs an error and the `errorCallback` is not a function.
	 */
	get(endpoint, body = {}, errorCallback = ApiClient.ERROR_FLASH, abortController = null)
	{
		return this._request("GET", endpoint, body, errorCallback, abortController);
	}

	/**
	 * @param {string} endpoint The route name of the API endpoint.
	 * @param {object} body The body of the request. Raw objects and FormData are accepted.
	 * @param {ApiClient.ERROR_FLASH|function|null} errorCallback Specifies what to do when a user-friendly
	 * 	error message is returned by the API. Available options:
	 * 	- `ApiClient.ERROR_FLASH`: automatically displays the error in a temporary Flash message, and then throws an `ApiError`. (default)
	 * 	- function: calls the provided callback with the error message as a parameter.
	 *  - null: throws an `ApiError` without providing any user feedback.
	 * @param {AbortController|null} abortController The abort controller to use for the request (optional)
	 * @returns {object|undefined} The response data object, or undefined if an error is returned from the API.
	 * @throws {ApiError} Thrown when the API reutnrs an error and the `errorCallback` is not a function.
	 */
	post(endpoint, body = {}, errorCallback = ApiClient.ERROR_FLASH, abortController = null)
	{
		return this._request("POST", endpoint, body, errorCallback, abortController);
	}

	/**
	 * @param {string} endpoint The route name of the API endpoint.
	 * @param {object} body The body of the request. Raw objects and FormData are accepted.
	 * @param {ApiClient.ERROR_FLASH|function|null} errorCallback Specifies what to do when a user-friendly
	 * 	error message is returned by the API. Available options:
	 * 	- `ApiClient.ERROR_FLASH`: automatically displays the error in a temporary Flash message, and then throws an `ApiError`. (default)
	 * 	- function: calls the provided callback with the error message as a parameter.
	 *  - null: throws an `ApiError` without providing any user feedback.
	 * @param {AbortController|null} abortController The abort controller to use for the request (optional)
	 * @returns {object|undefined} The response data object, or undefined if an error is returned from the API.
	 * @throws {ApiError} Thrown when the API reutnrs an error and the `errorCallback` is not a function.
	 */
	put(endpoint, body = {}, errorCallback = ApiClient.ERROR_FLASH, abortController = null)
	{
		return this._request("PUT", endpoint, body, errorCallback, abortController);
	}

	/**
	 * @param {string} endpoint The route name of the API endpoint.
	 * @param {object} body The body of the request. Raw objects and FormData are accepted.
	 * @param {ApiClient.ERROR_FLASH|function|null} errorCallback Specifies what to do when a user-friendly
	 * 	error message is returned by the API. Available options:
	 * 	- `ApiClient.ERROR_FLASH`: automatically displays the error in a temporary Flash message, and then throws an `ApiError`. (default)
	 * 	- function: calls the provided callback with the error message as a parameter.
	 *  - null: throws an `ApiError` without providing any user feedback.
	 * @param {AbortController|null} abortController The abort controller to use for the request (optional)
	 * @returns {object|undefined} The response data object, or undefined if an error is returned from the API.
	 * @throws {ApiError} Thrown when the API reutnrs an error and the `errorCallback` is not a function.
	 */
	patch(endpoint, body = {}, errorCallback = ApiClient.ERROR_FLASH, abortController = null)
	{
		return this._request("PATCH", endpoint, body, errorCallback, abortController);
	}

	/**
	 * @param {string} endpoint The route name of the API endpoint.
	 * @param {object} body The body of the request. Raw objects and FormData are accepted.
	 * @param {ApiClient.ERROR_FLASH|function|null} errorCallback Specifies what to do when a user-friendly
	 * 	error message is returned by the API. Available options:
	 * 	- `ApiClient.ERROR_FLASH`: automatically displays the error in a temporary Flash message, and then throws an `ApiError`. (default)
	 * 	- function: calls the provided callback with the error message as a parameter.
	 *  - null: throws an `ApiError` without providing any user feedback.
	 * @param {AbortController|null} abortController The abort controller to use for the request (optional)
	 * @returns {object|undefined} The response data object, or undefined if an error is returned from the API.
	 * @throws {ApiError} Thrown when the API reutnrs an error and the `errorCallback` is not a function.
	 */
	delete(endpoint, body = {}, errorCallback = ApiClient.ERROR_FLASH, abortController = null)
	{
		return this._request("DELETE", endpoint, body, errorCallback, abortController);
	}
}


const client = new ApiClient();

export default client;
