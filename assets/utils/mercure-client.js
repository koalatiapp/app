// @TODO: Add CSRF and/or session checks to API calls

let onmessageInitialized = false;
const callbacksByType = {};

/**
 * This callback type is called `requestCallback` and is displayed as a global symbol.
 *
 * @callback mercureEventListenerCallback
 * @param {object} update
 * @param {string} update.event Type of event (`create`, `update`, or `delete`)
 * @param {string} update.timestamp Timestamp at which the update was sent.
 * @param {string} update.type Type of entity this update is about.
 * @param {string} update.id ID of the entity this update is about.
 * @param {object} update.data Object representing the entity or the parts of the entity.
 */

/**
  * The `MercureClient` class handles all live updates sent from the server to the client..
  *
  * Every update is sent to the same Mercure topic, which is specific to the currently
  * logged in user. Each listener can check for a specific event type by verifying the
  * `event` property of received updates.
  */
class MercureClient {
	/**
	 * Subscribes to a Mercure topic and executes the provided callback wehenever an update is received.
	 *
	 * @param {string} type Type of entity for which to listen to events
	 * @param {mercureEventListenerCallback} updateCallback The callback that will run when an update is received.
	 *		The callback will receive as a parameter an object with the following properties:
	 * 			- {string} `event` Type of event (`create`, `update`, or `delete`)
	 * 			- {string} `timestamp` Timestamp at which the update was sent.
	 * 			- {string} `type` Type of entity this update is about.
	 * 			- {string} `id` ID of the entity this update is about.
	 * 			- {object} `data` Object representing the entity or the parts of the entity.
	 *
	 * @returns {EventSource} The EventSource that handles the subscription
	 */
	subscribe(entityType, updateCallback)
	{
		if (!onmessageInitialized) {
			window.mercureEventSource.onmessage = event => {
				const eventData = JSON.parse(event.data);

				for (const callback of callbacksByType[eventData.type] ?? []) {
					callback(eventData);
				}
			};
			onmessageInitialized = true;

			window.addEventListener("beforeunload", () => {
				window.mercureEventSource.close();
			});
		}

		if (typeof callbacksByType[entityType] == "undefined") {
			callbacksByType[entityType] = [];
		}

		callbacksByType[entityType].push(updateCallback);

		return window.mercureEventSource;
	}

	/**
	 * @param {string} type Type of entity for which to listen to events
	 * @param {mercureEventListenerCallback} updateCallback The callback that was set up to run when an update is received.
	 *
	 * @returns {EventSource} The EventSource that handles the subscription
	 */
	unsubscribe(entityType, updateCallback)
	{
		callbacksByType[entityType] = (callbacksByType[entityType] ?? []).filter(registeredCallback => {
			return registeredCallback != updateCallback;
		});
	}
}


const client = new MercureClient();

export default client;
