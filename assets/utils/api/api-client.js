import ApiError from "./api-error";

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
	 * @param {string} method The HTTP method to use for the request.
	 * @param {string} endpoint The route name of the API endpoint.
	 * @param {object} body The body of the request. Raw objects and FormData are accepted.
	 * @param {ApiClient.ERROR_FLASH|function|null} errorCallback Specifies what to do when a user-friendly
	 * 	error message is returned by the API. Available options:
	 * 	- `ApiClient.ERROR_FLASH`: automatically displays the error in a temporary Flash message.
	 * 	- function: calls the provided callback with the error message as a parameter
	 *  - null: throws an `ApiError`
	 * @returns {object|undefined} The response object, or undefined if an error is returned from the API.
	 * @throws {ApiError} Only thrown when the `errorCallback` is null or an unknown option.
	 */
	async _request(method, endpoint, body = {}, errorCallback = ApiClient.ERROR_FLASH)
	{
		method = method.trim().toUpperCase();

		if (!(body instanceof FormData) && method != "GET") {
			const formData = new FormData();

			for (const key in body) {
				formData.append(key, body[key]);
			}

			body = formData;
		}

		const url = Routing.generate(endpoint, method == "GET" ? body : {});
		const fetchOptions = {
			method: method,
		};

		if (method != "GET") {
			fetchOptions.body = body;
		}

		const encodedResponse = await fetch(url, fetchOptions);
		const response = await encodedResponse.json();

		if (response.status != "ok") {
			if (errorCallback == ApiClient.ERROR_FLASH) {
				window.Flash.show("danger", response.message);
				return;
			} else if (typeof errorCallback == "function") {
				errorCallback(response.message);
				return;
			} else {
				throw new ApiError(response.message);
			}
		}

		return response;
	}

	/**
	 * @param {string} endpoint The route name of the API endpoint.
	 * @param {object} body The body of the request. Raw objects and FormData are accepted.
	 * @param {ApiClient.ERROR_FLASH|function|null} errorCallback Specifies what to do when a user-friendly
	 * 	error message is returned by the API. Available options:
	 * 	- `ApiClient.ERROR_FLASH`: automatically displays the error in a temporary Flash message.
	 * 	- function: calls the provided callback with the error message as a parameter
	 *  - null: throws an `ApiError`
	 * @returns {object|undefined} The response object, or undefined if an error is returned from the API.
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
	 * @returns {object|undefined} The response object, or undefined if an error is returned from the API.
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
	 * @returns {object|undefined} The response object, or undefined if an error is returned from the API.
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
	 * @returns {object|undefined} The response object, or undefined if an error is returned from the API.
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
	 * @returns {object|undefined} The response object, or undefined if an error is returned from the API.
	 * @throws {ApiError} Only thrown when the `errorCallback` is null or an unknown option.
	 */
	delete(endpoint, body = {}, errorCallback = ApiClient.ERROR_FLASH)
	{
		return this._request("DELETE", endpoint, body, errorCallback);
	}
}


const client = new ApiClient();

export default client;
