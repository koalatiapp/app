import { NbList } from "../native-bear";
import { ApiClient } from "../utils/api";
import MercureClient from "../utils/mercure-client.js";
import fontawesomeImport from "../utils/fontawesome-import";

/**
 * Implements the basics for dynamic <nb-list> elemements that
 * fetch their content from the internal API.
 *
 * Has built-in support for live-updates with Mercure. You must define
 * the `supportedEntityType()` method (and optionally redefine the
 * `supportedDynamicActions()` method) to enable live-updates.
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

	render()
	{
		return [
			fontawesomeImport,
			super.render()
		];
	}

	firstUpdated()
	{
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
	 */
	fetchListData(endpoint, body = {})
	{
		this._isWaitingForServerResponse = true;

		ApiClient.get(endpoint, body).then(response => {
			this._isWaitingForServerResponse = false;
			this.items = Array.isArray(response.data) ? response.data : Object.values(response.data);

			this.dispatchEvent(new CustomEvent("items-initialized"));

			if (this.supportedEntityType()) {
				this.#mercureUpdateCallback = (update) => this.#processMercureUpdate(update);
				MercureClient.subscribe(this.supportedEntityType(), this.#mercureUpdateCallback);
			}
		});
	}

	#processMercureUpdate(update)
	{
		let itemsHaveChanged = false;

		if (update.data) {
			for (const index in this.items) {
				if (this.items[index].id == update.id) {
					if (this.supportsDynamicAction("update")) {
						const updatedList = [...this.items];
						updatedList[index] = update.data;
						this.items = updatedList;
						itemsHaveChanged = true;
					}
					break;
				}
			}

			if (!itemsHaveChanged) {
				// If we got here, it means the item wasn't found - add it to the list.
				if (this.supportsDynamicAction("add")) {
					this.items = this.items.concat([update.data]);
				}
			}
		} else if (this.supportsDynamicAction("delete")) {
			const originalLength = this.items.length;
			this.items = this.items.filter(item => item.id != update.id);

			if (this.items.length != originalLength) {
				itemsHaveChanged = true;
			}
		}

		if (itemsHaveChanged) {
			this.dispatchEvent(new CustomEvent("items-updated"));
		}
	}
}
