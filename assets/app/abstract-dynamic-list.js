import { NbList } from "../native-bear";
import { ApiClient } from "../utils/api";
import fontawesomeImport from "../utils/fontawesome-import";

/**
 * Implements the basics for dynamic <nb-list> elemements that
 * fetch their content from the internal API.
 *
 * Has built-in support for live-updates with Mercure with the
 * custom `suggested-mercure-topic` header returned by the API.
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
		if (this.isLoading) {
			this.fetchListData();
		}
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
		ApiClient.get(endpoint, body).then(response => {
			this.items = Array.isArray(response.data) ? response.data : Object.values(response.data);

			this.dispatchEvent(new CustomEvent("items-initialized"));

			// Subscribe to live updates
			const mercureTopic = response._response.headers.get("suggested-mercure-topic");
			if (mercureTopic) {
				ApiClient.subscribe(mercureTopic, update => {
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
				});
			}
		});
	}
}
