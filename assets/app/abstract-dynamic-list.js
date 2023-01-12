import { NbList } from "../native-bear";
import { ApiClient } from "../utils/api";
import MercureClient from "../utils/mercure-client.js";
import fontawesomeImport from "../utils/fontawesome-import";

/**
 * Implements the basics for dynamic <nb-list> elemements that
 * fetch their content from the internal API.
 *
 * Has built-in support for live-updates with Mercure. You must define
 * the `supportedEntityType()` and the `supportedUpdateFilter`() methods
 * (and optionally redefine the `supportedDynamicActions()` method) to
 * enable live-updates.
 *
 * When extending this class, you must redefine the `fetchListData`
 * method and call `super.fetchListData()` with the desired endpoint
 * and request body object.
 *
 * Ex.:
 * ```
 * fetchListData()
 * {
 *   super.fetchListData("api_endpoint_here", { some_param: this.someParam });
 * }
 * ```
 */
export class AbstractDynamicList extends NbList {
	#mercureUpdateCallback = null;

	constructor()
	{
		super();
		this._isWaitingForServerResponse = false;
	}

	connectedCallback()
	{
		super.connectedCallback();

		this.addEventListener("items-initialized", () => {
			this.#initMercureUpdateListener();
		}, { once: true });
	}

	render()
	{
		return [
			fontawesomeImport,
			super.render()
		];
	}

	firstUpdated()
	{
		if (this.items) {
			this.dispatchEvent(new CustomEvent("items-initialized"));
			return;
		}

		this.fetchListData();
	}

	updated()
	{
		// In some cases, attributes may be set after the initial render,
		// which causes a list that is "stuck" in the loading state.
		// By checking on subsequent updates, we can avoid this.
		if (this.isLoading && !this._isWaitingForServerResponse) {
			this.fetchListData();
		}
	}

	disconnectedCallback()
	{
		if (this.#mercureUpdateCallback) {
			MercureClient.unsubscribe(this.supportedEntityType(), this.#mercureUpdateCallback);
		}

		super.disconnectedCallback();
	}

	/**
	 * Returns the list of dynamic actions that are supported by this list.
	 * Available actions:
	 * - `"update"`
	 * - `"add"`
	 * - `"delete"`
	 *
	 * @returns {string[]} Array of supported actions
	*/
	supportedDynamicActions()
	{
		return ["update", "add", "delete"];
	}

	/**
	 * Returns the type of entity this list supports.
	 *
	 * @returns {string|null}
	*/
	supportedEntityType()
	{
		return null;
	}

	/**
	 * Receives an update as an argument and returns
	 * a boolean indicating whether the update should
	 * be treated by this component or not.
	 *
	 *
	 * @returns {function}
	*/
	supportedUpdateFilter(update) // eslint-disable-line no-unused-vars
	{
		return true;
	}

	/**
	 * Whether the given action is supported or not by the list.
	 * Available actions:
	 * - `"update"`
	 * - `"add"`
	 * - `"delete"`
	 *
	 * @final
	 * @param {string} action
	 * @returns {boolean} Whether the action is supported by this list.
	 */
	supportsDynamicAction(action)
	{
		return this.supportedDynamicActions().includes(action.trim().toLowerCase());
	}

	/**
	 * @param {string} endpoint
	 * @param {FormData|object} body
	 * @param {string} itemsResponseKey Key to use to retrieve the items from the API's response.
	 */
	fetchListData(endpoint, body = {}, itemsResponseKey = "hydra:member")
	{
		this._isWaitingForServerResponse = true;

		ApiClient.get(endpoint, body).then(response => {
			this._isWaitingForServerResponse = false;
			this.items = response[itemsResponseKey] ?? [];
			// @TODO: Add support for JSON+LD pagination in dynamic lists

			this.dispatchEvent(new CustomEvent("items-initialized"));
		});
	}

	#initMercureUpdateListener()
	{
		if (this.supportedEntityType()) {
			this.#mercureUpdateCallback = (update) => this.#processMercureUpdate(update);
			MercureClient.subscribe(this.supportedEntityType(), this.#mercureUpdateCallback);
		}
	}

	#processMercureUpdate(update)
	{
		console.log(update);
		if (!this.supportedUpdateFilter(update)) {
			// The component doesn't support that update.
			return;
		}

		let itemsHaveChanged = false;

		switch (update.event) {
		case "delete":
			if (this.supportsDynamicAction("delete")) {
				const originalLength = this.items.length;
				this.items = this.items.filter(item => item.id != update.id);

				if (this.items.length != originalLength) {
					itemsHaveChanged = true;
				}
			}
			break;

		case "update":
			if (this.supportsDynamicAction("update")) {
				for (const index in this.items) {
					if (this.items[index].id == update.id) {
						const updatedList = [...this.items];
						updatedList[index] = update.data;
						this.items = updatedList;
						itemsHaveChanged = true;
						break;
					}
				}
			}

			// If we couldn't find the item to update, create it.
			if (!itemsHaveChanged && this.supportsDynamicAction("add")) {
				this.items = this.items.concat([update.data]);
				itemsHaveChanged = true;
			}
			break;

		case "create":
			if (this.supportsDynamicAction("add")) {
				this.items = this.items.concat([update.data]);
				itemsHaveChanged = true;
			}
			break;
		}

		if (itemsHaveChanged) {
			this.dispatchEvent(new CustomEvent("items-updated"));
		}
	}
}
