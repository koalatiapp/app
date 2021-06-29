import { NbList } from "../native-bear";
import { ApiClient } from "../utils/api";
import fontawesomeImport from "../utils/fontawesome-import";

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
