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

	fetchListData(endpoint, body = {})
	{
		ApiClient.get(endpoint, body).then(response => {
			this.items = response.data;

			// Subscribe to live updates
			const mercureTopic = response._response.headers.get("suggested-mercure-topic");
			if (mercureTopic) {
				ApiClient.subscribe(mercureTopic, update => {
					if (update.data) {
						this.items = this.items.concat([update.data]);
					} else {
						this.items = this.items.filter(item => item.id != update.id);
					}
				});
			}
		});
	}
}
