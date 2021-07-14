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
	 * 	- `ApiClient.ERROR_FLASH`: automatically displays the error in a temporary Flash message.
	 * 	- function: calls the provided callback with the error message as a parameter
	 *  - null: throws an `ApiError`
	 * @returns {object|undefined} The response data object, or undefined if an error is returned from the API.
	 * @throws {ApiError} Only thrown when the `errorCallback` is null or an unknown option.
	 */
	async _request(method, endpoint, body = {}, errorCallback = ApiClient.ERROR_FLASH)
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

		if (!["GET", "DELETE"].includes(method)) {
			fetchOptions.body = body;
		}

		const response = await fetch(url, fetchOptions);
		const responseData = await response.json();

		if (responseData.status != "ok") {
			if (errorCallback == ApiClient.ERROR_FLASH) {
				window.Flash.show("danger", responseData.message);
				return;
			} else if (typeof errorCallback == "function") {
				errorCallback(responseData.message);
				return;
			} else {
				throw new ApiError(responseData.message);
			}
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
	 * 	- `ApiClient.ERROR_FLASH`: automatically displays the error in a temporary Flash message.
	 * 	- function: calls the provided callback with the error message as a parameter
	 *  - null: throws an `ApiError`
	 * @returns {Promise<object|undefined>} The response object, or undefined if an error is returned from the API.
	 * @throws {ApiError} Only thrown when the `errorCallback` is null or an unknown option.
	 */
	get(endpoint, body = {}, errorCallback = ApiClient.ERROR_FLASH)
	{
		return this._request("GET", endpoint, body, errorCallback);
	}

	/**
	 * @param {string} endpoint The route name of the API endpoint.
	 * @param {object} body The body of the request. Raw objects and FormData are accepted.
	 * @param {ApiClient.ERROR_FLASH|function|null} errorCallback Specifies what to do when a user-friendly
	 * 	error message is returned by the API. Available options:
	 * 	- `ApiClient.ERROR_FLASH`: automatically displays the error in a temporary Flash message.
	 * 	- function: calls the provided callback with the error message as a parameter
	 *  - null: throws an `ApiError`
	 * @returns {Promise<object|undefined>} The response object, or undefined if an error is returned from the API.
	 * @throws {ApiError} Only thrown when the `errorCallback` is null or an unknown option.
	 */
	post(endpoint, body = {}, errorCallback = ApiClient.ERROR_FLASH)
	{
		return this._request("POST", endpoint, body, errorCallback);
	}

	/**
	 * @param {string} endpoint The route name of the API endpoint.
	 * @param {object} body The body of the request. Raw objects and FormData are accepted.
	 * @param {ApiClient.ERROR_FLASH|function|null} errorCallback Specifies what to do when a user-friendly
	 * 	error message is returned by the API. Available options:
	 * 	- `ApiClient.ERROR_FLASH`: automatically displays the error in a temporary Flash message.
	 * 	- function: calls the provided callback with the error message as a parameter
	 *  - null: throws an `ApiError`
	 * @returns {Promise<object|undefined>} The response object, or undefined if an error is returned from the API.
	 * @throws {ApiError} Only thrown when the `errorCallback` is null or an unknown option.
	 */
	put(endpoint, body = {}, errorCallback = ApiClient.ERROR_FLASH)
	{
		return this._request("PUT", endpoint, body, errorCallback);
	}

	/**
	 * @param {string} endpoint The route name of the API endpoint.
	 * @param {object} body The body of the request. Raw objects and FormData are accepted.
	 * @param {ApiClient.ERROR_FLASH|function|null} errorCallback Specifies what to do when a user-friendly
	 * 	error message is returned by the API. Available options:
	 * 	- `ApiClient.ERROR_FLASH`: automatically displays the error in a temporary Flash message.
	 * 	- function: calls the provided callback with the error message as a parameter
	 *  - null: throws an `ApiError`
	 * @returns {Promise<object|undefined>} The response object, or undefined if an error is returned from the API.
	 * @throws {ApiError} Only thrown when the `errorCallback` is null or an unknown option.
	 */
	patch(endpoint, body = {}, errorCallback = ApiClient.ERROR_FLASH)
	{
		return this._request("PATCH", endpoint, body, errorCallback);
	}

	/**
	 * @param {string} endpoint The route name of the API endpoint.
	 * @param {object} body The body of the request. Raw objects and FormData are accepted.
	 * @param {ApiClient.ERROR_FLASH|function|null} errorCallback Specifies what to do when a user-friendly
	 * 	error message is returned by the API. Available options:
	 * 	- `ApiClient.ERROR_FLASH`: automatically displays the error in a temporary Flash message.
	 * 	- function: calls the provided callback with the error message as a parameter
	 *  - null: throws an `ApiError`
	 * @returns {Promise<object|undefined>} The response object, or undefined if an error is returned from the API.
	 * @throws {ApiError} Only thrown when the `errorCallback` is null or an unknown option.
	 */
	delete(endpoint, body = {}, errorCallback = ApiClient.ERROR_FLASH)
	{
		return this._request("DELETE", endpoint, body, errorCallback);
	}

	/**
	 * Subscribes to a Mercure topic and executes the provided callback wehenever an update is received.
	 *
	 * @param {string} endpoint The route name of the API endpoint.
	 * @param {object} body The body of the request. Raw objects and FormData are accepted.
	 * @param {function} updateCallback The callback that will run when an update is received.
	 * @returns {EventSource} The EventSource that handles the subscription
	 * @throws {ApiError} Only thrown when the `errorCallback` is null or an unknown option.
	 */
	subscribe(topic, updateCallback = () => {})
	{
		const baseUrl = Routing.getScheme() + "://" + Routing.getHost();
		const sourceUrl = baseUrl + "/.well-known/mercure?topic=" + encodeURIComponent(topic);
		const eventSource = new EventSource(sourceUrl, {
			withCredentials: true
		});

		eventSource.onmessage = event => {
			updateCallback(JSON.parse(event.data));
		};

		return eventSource;
	}
}


const client = new ApiClient();

export default client;
